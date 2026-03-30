(() => {
    'use strict';

    const form = document.getElementById('article-form');
    if (!form) {
        return;
    }

    const editor = form.querySelector('[data-hook="visual-editor"]');
    const hiddenContent = form.querySelector('[data-hook="content-hidden"]');
    const toolbar = form.querySelector('[data-hook="wysiwyg-toolbar"]');
    const mediaList = form.querySelector('[data-hook="media-list"]');
    const mediaUploadInput = form.querySelector('[data-hook="inline-media-upload"]');
    const mediaFeedback = form.querySelector('[data-hook="media-feedback"]');

    if (!(editor instanceof HTMLElement) || !(hiddenContent instanceof HTMLTextAreaElement)) {
        return;
    }

    const csrfTokenInput = form.querySelector('input[name="_token"]');
    const csrfToken = csrfTokenInput instanceof HTMLInputElement ? csrfTokenInput.value : '';

    const endpointList = '/admin/media/images';
    const endpointUpload = '/admin/media/upload';

    let savedRange = null;

    initializeEditor();
    bindToolbar();
    bindSync();
    bindImageAltEditor();
    bindDropZone();
    bindMediaUpload();
    loadMediaLibrary();

    function initializeEditor() {
        if (hiddenContent.value.trim() !== '') {
            editor.innerHTML = hiddenContent.value;
        }

        if (editor.innerHTML.trim() === '') {
            editor.innerHTML = '<p><br></p>';
        }

        syncHiddenContent();
        refreshToolbarState();
    }

    function bindToolbar() {
        if (!(toolbar instanceof HTMLElement)) {
            return;
        }

        toolbar.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            const button = target.closest('button[data-action]');
            if (!(button instanceof HTMLButtonElement)) {
                return;
            }

            event.preventDefault();
            applyToolbarAction(button);
        });

        document.addEventListener('selectionchange', () => {
            if (document.activeElement === editor || editor.contains(document.activeElement)) {
                saveSelection();
                refreshToolbarState();
            }
        });

        editor.addEventListener('keyup', refreshToolbarState);
        editor.addEventListener('mouseup', refreshToolbarState);
    }

    function applyToolbarAction(button) {
        const action = button.dataset.action || '';
        const value = button.dataset.value || '';

        editor.focus();
        restoreSelection();

        if (action === 'createLink') {
            const selectedText = getCurrentSelectionText();
            const defaultUrl = selectedText.startsWith('http://') || selectedText.startsWith('https://')
                ? selectedText
                : 'https://';

            const href = window.prompt('URL du lien :', defaultUrl);
            if (href === null) {
                return;
            }

            const normalizedHref = href.trim();
            if (normalizedHref === '') {
                return;
            }

            document.execCommand('createLink', false, normalizedHref);
            syncHiddenContent();
            refreshToolbarState();
            return;
        }

        if (action === 'formatBlock') {
            document.execCommand('formatBlock', false, value);
            syncHiddenContent();
            refreshToolbarState();
            return;
        }

        document.execCommand(action, false, value || null);
        syncHiddenContent();
        refreshToolbarState();
    }

    function bindSync() {
        editor.addEventListener('input', () => {
            saveSelection();
            syncHiddenContent();
        });

        editor.addEventListener('blur', saveSelection);

        form.addEventListener('submit', () => {
            syncHiddenContent();
        });
    }

    function syncHiddenContent() {
        hiddenContent.value = editor.innerHTML.trim();
    }

    function bindDropZone() {
        editor.addEventListener('dragover', (event) => {
            event.preventDefault();
            editor.classList.add('is-drop-target');
        });

        editor.addEventListener('dragleave', () => {
            editor.classList.remove('is-drop-target');
        });

        editor.addEventListener('drop', (event) => {
            event.preventDefault();
            editor.classList.remove('is-drop-target');

            const transfer = event.dataTransfer;
            if (!transfer) {
                return;
            }

            const imageUrl = transfer.getData('application/x-media-url') || transfer.getData('text/plain');
            if (!imageUrl) {
                return;
            }

            const imageName = transfer.getData('application/x-media-name') || extractFilename(imageUrl);
            insertImageFigure(imageUrl, imageName, event.clientX, event.clientY);
        });
    }

    function bindMediaUpload() {
        if (!(mediaUploadInput instanceof HTMLInputElement)) {
            return;
        }

        mediaUploadInput.addEventListener('change', async () => {
            const files = mediaUploadInput.files;
            if (!files || files.length === 0) {
                return;
            }

            const file = files[0];
            setMediaFeedback('Upload en cours...', 'info');

            try {
                const uploaded = await uploadInlineMedia(file);
                prependMediaItem(uploaded.url, extractFilename(uploaded.path || file.name));
                insertImageFigure(uploaded.url, file.name);
                setMediaFeedback('Image uploadée et insérée.', 'success');
            } catch (error) {
                const message = error instanceof Error ? error.message : 'Echec de l\'upload.';
                setMediaFeedback(message, 'error');
            } finally {
                mediaUploadInput.value = '';
            }
        });
    }

    async function loadMediaLibrary() {
        if (!(mediaList instanceof HTMLElement)) {
            return;
        }

        setMediaFeedback('Chargement de la bibliothèque...', 'info');

        try {
            const response = await fetch(endpointList, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });

            const payload = await parseJsonResponse(response, 'Chargement de la bibliotheque');
            const images = extractImages(payload);

            renderMediaList(images);
            setMediaFeedback(images.length > 0 ? `${images.length} image(s) disponible(s).` : 'Aucune image disponible.', 'info');
        } catch (error) {
            renderMediaList([]);
            setMediaFeedback('Impossible de charger les médias.', 'error');
        }
    }

    function extractImages(payload) {
        if (!payload || payload.success !== true || !payload.data || !Array.isArray(payload.data.images)) {
            return [];
        }

        return payload.data.images
            .filter((item) => item && typeof item.url === 'string')
            .map((item) => ({
                url: item.url,
                name: typeof item.name === 'string' ? item.name : extractFilename(item.url),
            }));
    }

    function renderMediaList(images) {
        if (!(mediaList instanceof HTMLElement)) {
            return;
        }

        mediaList.innerHTML = '';

        if (images.length === 0) {
            mediaList.innerHTML = '<p class="media-empty">Aucune image.</p>';
            return;
        }

        const fragment = document.createDocumentFragment();
        images.forEach((image) => {
            fragment.appendChild(createMediaCard(image.url, image.name));
        });

        mediaList.appendChild(fragment);
    }

    function prependMediaItem(url, name) {
        if (!(mediaList instanceof HTMLElement)) {
            return;
        }

        const card = createMediaCard(url, name);
        const empty = mediaList.querySelector('.media-empty');
        if (empty) {
            empty.remove();
        }

        mediaList.prepend(card);
    }

    function createMediaCard(url, name) {
        const card = document.createElement('button');
        card.type = 'button';
        card.className = 'media-item';
        card.dataset.url = url;
        card.dataset.name = name;
        card.draggable = true;
        card.title = 'Cliquer pour inserer ou glisser-deposer dans l\'editeur';

        const preview = document.createElement('img');
        preview.src = url;
        preview.alt = name;
        preview.loading = 'lazy';

        const label = document.createElement('span');
        label.className = 'media-item-name';
        label.textContent = name;

        card.appendChild(preview);
        card.appendChild(label);

        card.addEventListener('click', () => {
            insertImageFigure(url, name);
        });

        card.addEventListener('dragstart', (event) => {
            if (!event.dataTransfer) {
                return;
            }

            event.dataTransfer.effectAllowed = 'copy';
            event.dataTransfer.setData('application/x-media-url', url);
            event.dataTransfer.setData('application/x-media-name', name);
            event.dataTransfer.setData('text/plain', url);
        });

        return card;
    }

    async function uploadInlineMedia(file) {
        const body = new FormData();
        body.append('image', file);
        body.append('_token', csrfToken);

        const response = await fetch(endpointUpload, {
            method: 'POST',
            body,
            headers: {
                'Accept': 'application/json',
            },
        });

        const payload = await parseJsonResponse(response, 'Upload image');
        if (!response.ok || !payload || payload.success !== true || !payload.data || typeof payload.data.url !== 'string') {
            const message = payload && payload.error && typeof payload.error.message === 'string'
                ? payload.error.message
                : 'Erreur pendant l\'upload.';
            throw new Error(message);
        }

        return payload.data;
    }

    async function parseJsonResponse(response, contextLabel) {
        const rawText = await response.text();
        const trimmed = rawText.trim();

        if (trimmed === '') {
            throw new Error(`${contextLabel}: reponse vide du serveur.`);
        }

        try {
            return JSON.parse(trimmed);
        } catch (error) {
            if (looksLikeHtml(trimmed)) {
                throw new Error(`${contextLabel}: le serveur a renvoye du HTML au lieu de JSON (souvent une erreur PHP/permissions upload).`);
            }

            throw new Error(`${contextLabel}: reponse serveur invalide (JSON attendu).`);
        }
    }

    function looksLikeHtml(value) {
        return /^\s*<(?:!doctype\s+html|html|body|head|br|p|div)/i.test(value);
    }

    function insertImageFigure(url, fileName, dropX, dropY) {
        editor.focus();

        let range = null;
        if (typeof dropX === 'number' && typeof dropY === 'number') {
            range = getRangeFromPoint(dropX, dropY);
        }

        if (!range) {
            restoreSelection();
            range = savedRange;
        }

        if (!range) {
            range = document.createRange();
            range.selectNodeContents(editor);
            range.collapse(false);
        }

        if (!(range instanceof Range)) {
            return;
        }

        const alt = window.prompt('Texte alternatif (alt) :', fileName) || '';
        const figure = createFigureNode(url, fileName, alt.trim());

        const paragraph = document.createElement('p');
        paragraph.appendChild(document.createElement('br'));

        const fragment = document.createDocumentFragment();
        fragment.appendChild(figure);
        fragment.appendChild(paragraph);

        range.deleteContents();
        range.insertNode(fragment);

        const selection = window.getSelection();
        if (selection) {
            const after = document.createRange();
            after.setStart(paragraph, 0);
            after.collapse(true);
            selection.removeAllRanges();
            selection.addRange(after);
            savedRange = after;
        }

        syncHiddenContent();
    }

    function createFigureNode(url, fileName, altText) {
        const figure = document.createElement('figure');
        figure.className = 'editor-figure';
        figure.contentEditable = 'false';

        const image = document.createElement('img');
        image.src = url;
        image.alt = altText;
        image.className = 'editor-inline-image';

        const caption = document.createElement('figcaption');
        caption.contentEditable = 'true';
        caption.className = 'editor-figcaption';
        caption.textContent = 'Legende (modifier ce texte)';

        figure.appendChild(image);
        figure.appendChild(caption);

        return figure;
    }

    function bindImageAltEditor() {
        editor.addEventListener('dblclick', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLImageElement)) {
                return;
            }

            if (!target.classList.contains('editor-inline-image')) {
                return;
            }

            const nextAlt = window.prompt('Modifier le texte alternatif :', target.alt || '');
            if (nextAlt === null) {
                return;
            }

            target.alt = nextAlt.trim();
            syncHiddenContent();
        });
    }

    function setMediaFeedback(message, level) {
        if (!(mediaFeedback instanceof HTMLElement)) {
            return;
        }

        mediaFeedback.textContent = message;
        mediaFeedback.dataset.level = level;
    }

    function refreshToolbarState() {
        if (!(toolbar instanceof HTMLElement)) {
            return;
        }

        const buttons = toolbar.querySelectorAll('button[data-action]');
        buttons.forEach((button) => {
            if (!(button instanceof HTMLButtonElement)) {
                return;
            }

            const action = button.dataset.action || '';
            const value = (button.dataset.value || '').toLowerCase();

            let active = false;

            if (action === 'formatBlock') {
                const current = String(document.queryCommandValue('formatBlock') || '').replace(/[<>]/g, '').toLowerCase();
                active = current === value;
            } else if (
                action === 'bold' ||
                action === 'italic' ||
                action === 'underline' ||
                action === 'insertUnorderedList' ||
                action === 'insertOrderedList' ||
                action === 'justifyLeft' ||
                action === 'justifyCenter' ||
                action === 'justifyRight'
            ) {
                active = document.queryCommandState(action);
            }

            button.classList.toggle('is-active', active);
        });
    }

    function saveSelection() {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) {
            return;
        }

        const range = selection.getRangeAt(0);
        if (!editor.contains(range.commonAncestorContainer)) {
            return;
        }

        savedRange = range.cloneRange();
    }

    function restoreSelection() {
        if (!(savedRange instanceof Range)) {
            return;
        }

        const selection = window.getSelection();
        if (!selection) {
            return;
        }

        selection.removeAllRanges();
        selection.addRange(savedRange);
    }

    function getRangeFromPoint(clientX, clientY) {
        if (document.caretRangeFromPoint) {
            return document.caretRangeFromPoint(clientX, clientY);
        }

        if (document.caretPositionFromPoint) {
            const position = document.caretPositionFromPoint(clientX, clientY);
            if (!position) {
                return null;
            }

            const range = document.createRange();
            range.setStart(position.offsetNode, position.offset);
            range.collapse(true);
            return range;
        }

        return null;
    }

    function getCurrentSelectionText() {
        const selection = window.getSelection();
        return selection ? String(selection.toString() || '').trim() : '';
    }

    function extractFilename(path) {
        if (typeof path !== 'string' || path.trim() === '') {
            return 'image';
        }

        const cleaned = path.split('?')[0].split('#')[0];
        const chunks = cleaned.split('/').filter(Boolean);
        return chunks.length > 0 ? chunks[chunks.length - 1] : 'image';
    }
})();
