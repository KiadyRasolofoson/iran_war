(() => {
    'use strict';

    const form = document.getElementById('article-form');
    if (!form) {
        return;
    }

    const editor = form.querySelector('[data-hook="visual-editor"]');
    const hiddenContent = form.querySelector('[data-hook="content-hidden"]');
    const toolbar = form.querySelector('[data-hook="wysiwyg-toolbar"]');
    const editorContainer = form.querySelector('[data-hook="wysiwyg-container"]');
    const mediaList = form.querySelector('[data-hook="media-list"]');
    const mediaUploadInput = form.querySelector('[data-hook="inline-media-upload"]');
    const mediaFeedback = form.querySelector('[data-hook="media-feedback"]');

    if (!(editor instanceof HTMLElement) || !(hiddenContent instanceof HTMLTextAreaElement)) {
        return;
    }

    const csrfTokenInput = form.querySelector('input[name="_token"]');
    const csrfToken = csrfTokenInput instanceof HTMLInputElement ? csrfTokenInput.value : '';
    let mediaScopeInput = form.querySelector('input[name="media_scope"], #media-scope');
    let mediaScope = resolveMediaScope(mediaScopeInput);

    const endpointList = '/admin/media/images';
    const endpointUpload = '/admin/media/upload';

    let savedRange = null;
    let fullscreenButton = null;
    let activeImageResize = null;
    let resizeDragState = null;
    let draggedFigure = null;

    initializeEditor();
    bindToolbar();
    bindSync();
    bindFullscreen();
    bindImageAltEditor();
    bindImageResize();
    bindFigureDragMove();
    bindDropZone();
    bindMediaUpload();
    bindTemplateDelete();
    loadMediaLibrary();

    function initializeEditor() {
        if (hiddenContent.value.trim() !== '') {
            editor.innerHTML = hiddenContent.value;
        }

        if (editor.innerHTML.trim() === '') {
            editor.innerHTML = '<p><br></p>';
        }

        injectEditorControlStyles();
        ensureTemplateDeleteControls();

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

        if (action === 'insert-template') {
            insertTemplateBlock(button.dataset.template || '');
            return;
        }

        if (action === 'toggle-fullscreen') {
            toggleFullscreen(button);
            return;
        }

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
            ensureTemplateDeleteControls();
            saveSelection();
            syncHiddenContent();
        });

        editor.addEventListener('blur', saveSelection);

        form.addEventListener('submit', () => {
            clearImageResizeOverlay();
            syncHiddenContent();
        });
    }

    function bindFullscreen() {
        if (!(toolbar instanceof HTMLElement)) {
            return;
        }

        const button = toolbar.querySelector('button[data-action="toggle-fullscreen"]');
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        fullscreenButton = button;
        updateFullscreenButtonLabel(false);

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            if (!isFullscreenActive()) {
                return;
            }

            event.preventDefault();
            setFullscreen(false);
        });
    }

    function toggleFullscreen(button) {
        if (button instanceof HTMLButtonElement) {
            fullscreenButton = button;
        }

        setFullscreen(!isFullscreenActive());
    }

    function setFullscreen(enabled) {
        if (!(editorContainer instanceof HTMLElement)) {
            return;
        }

        editorContainer.classList.toggle('is-fullscreen', enabled);
        document.body.classList.toggle('editor-fullscreen-active', enabled);
        updateFullscreenButtonLabel(enabled);
    }

    function isFullscreenActive() {
        return editorContainer instanceof HTMLElement && editorContainer.classList.contains('is-fullscreen');
    }

    function updateFullscreenButtonLabel(isActive) {
        if (!(fullscreenButton instanceof HTMLButtonElement)) {
            return;
        }

        const label = isActive ? 'Quitter plein ecran' : 'Plein ecran';
        fullscreenButton.textContent = label;
        fullscreenButton.title = label;
        fullscreenButton.setAttribute('aria-label', label);
        fullscreenButton.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    }

    function insertTemplateBlock(templateName) {
        const block = createTemplateNode(templateName);
        if (!(block instanceof HTMLElement)) {
            return;
        }

        insertBlockAtSelection(block, { preferOutsideTemplate: true });
    }

    function createTemplateNode(templateName) {
        if (templateName === 'hero') {
            const section = document.createElement('div');
            section.className = 'editor-template editor-template-hero';

            const inner = document.createElement('div');

            const title = document.createElement('h2');
            title.textContent = 'Titre hero';

            const paragraph = document.createElement('p');
            paragraph.textContent = 'Ajoutez une introduction marquante ici.';

            inner.appendChild(title);
            inner.appendChild(paragraph);
            section.appendChild(inner);
            addTemplateDeleteControl(section);
            return section;
        }

        if (templateName === 'columns') {
            const container = document.createElement('div');
            container.className = 'editor-template editor-template-columns template-columns';

            const leftColumn = document.createElement('div');
            const rightColumn = document.createElement('div');
            leftColumn.className = 'template-column editor-template-column';
            rightColumn.className = 'template-column editor-template-column';

            const leftTitle = document.createElement('h3');
            leftTitle.textContent = 'Colonne 1';
            const leftParagraph = document.createElement('p');
            leftParagraph.textContent = 'Contenu de la premiere colonne.';

            const rightTitle = document.createElement('h3');
            rightTitle.textContent = 'Colonne 2';
            const rightParagraph = document.createElement('p');
            rightParagraph.textContent = 'Contenu de la seconde colonne.';

            leftColumn.appendChild(leftTitle);
            leftColumn.appendChild(leftParagraph);
            rightColumn.appendChild(rightTitle);
            rightColumn.appendChild(rightParagraph);
            container.appendChild(leftColumn);
            container.appendChild(rightColumn);

            addTemplateDeleteControl(container);

            return container;
        }

        if (templateName === 'quote') {
            const quote = document.createElement('blockquote');
            quote.className = 'editor-template editor-template-quote';
            const paragraph = document.createElement('p');
            paragraph.textContent = 'Ajoutez une citation forte.';
            quote.appendChild(paragraph);
            addTemplateDeleteControl(quote);
            return quote;
        }

        return null;
    }

    function syncHiddenContent() {
        const cleanHtml = getSanitizedEditorHtml();
        hiddenContent.value = cleanHtml;
    }

    function getSanitizedEditorHtml() {
        const clone = editor.cloneNode(true);
        if (!(clone instanceof HTMLElement)) {
            return editor.innerHTML.trim();
        }

        const helperSelectors = [
            '[data-editor-helper="true"]',
            '.editor-figure-delete',
            '.editor-template-delete',
        ];

        clone.querySelectorAll(helperSelectors.join(',')).forEach((node) => {
            node.remove();
        });

        return clone.innerHTML.trim();
    }

    function bindTemplateDelete() {
        editor.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            const button = target.closest('button[data-action="delete-template"]');
            if (!(button instanceof HTMLButtonElement) || !editor.contains(button)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const template = button.closest('.editor-template-hero, .editor-template-columns, .editor-template-quote');
            if (!(template instanceof HTMLElement) || !editor.contains(template)) {
                return;
            }

            removeTemplateBlock(template);
        });
    }

    function ensureTemplateDeleteControls() {
        const templates = editor.querySelectorAll('.editor-template-hero, .editor-template-columns, .editor-template-quote');
        templates.forEach((template) => {
            if (template instanceof HTMLElement) {
                addTemplateDeleteControl(template);
            }
        });
    }

    function addTemplateDeleteControl(template) {
        if (!(template instanceof HTMLElement)) {
            return;
        }

        if (template.querySelector(':scope > .editor-template-delete')) {
            return;
        }

        template.classList.add('editor-template-removable');

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'editor-template-delete';
        deleteButton.textContent = 'Supprimer';
        deleteButton.dataset.action = 'delete-template';
        deleteButton.dataset.editorHelper = 'true';
        deleteButton.contentEditable = 'false';
        deleteButton.setAttribute('aria-label', 'Supprimer ce bloc de contenu');

        template.insertBefore(deleteButton, template.firstChild);
    }

    function removeTemplateBlock(template) {
        if (!(template instanceof HTMLElement) || !editor.contains(template)) {
            return;
        }

        const nextParagraph = document.createElement('p');
        nextParagraph.appendChild(document.createElement('br'));

        template.insertAdjacentElement('afterend', nextParagraph);
        template.remove();

        const selection = window.getSelection();
        if (selection) {
            const range = document.createRange();
            range.setStart(nextParagraph, 0);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
            savedRange = range.cloneRange();
        }

        editor.focus();
        syncHiddenContent();
        refreshToolbarState();
    }

    function injectEditorControlStyles() {
        const styleId = 'editor-control-style';
        if (document.getElementById(styleId)) {
            return;
        }

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = [
            '.editor-template-removable { position: relative; }',
            '.editor-template-delete {',
            '  position: absolute;',
            '  top: 0.5rem;',
            '  right: 0.5rem;',
            '  border: 0;',
            '  border-radius: 999px;',
            '  padding: 0.3rem 0.55rem;',
            '  font-size: 0.75rem;',
            '  line-height: 1;',
            '  color: #ffffff;',
            '  background: rgba(185, 28, 28, 0.95);',
            '  cursor: pointer;',
            '  z-index: 2;',
            '}',
            '.editor-template-delete:hover { background: rgba(153, 27, 27, 0.98); }',
        ].join('\n');

        document.head.appendChild(style);
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
            if (draggedFigure instanceof HTMLElement) {
                return;
            }

            event.preventDefault();
            editor.classList.remove('is-drop-target');

            const transfer = event.dataTransfer;
            if (!transfer) {
                return;
            }

            const imageUrl = transfer.getData('application/x-media-url') || transfer.getData('text/plain');
            if (!imageUrl || imageUrl === 'figure-move') {
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
            const response = await fetch(buildMediaListEndpoint(endpointList, mediaScope), {
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
        if (mediaScope !== '') {
            body.append('scope', mediaScope);
        }

        const response = await fetch(endpointUpload, {
            method: 'POST',
            body,
            headers: {
                'Accept': 'application/json',
            },
        });

        const payload = await parseJsonResponse(response, 'Upload image');
        if (!response.ok || !payload || payload.success !== true || !payload.data || typeof payload.data.url !== 'string') {
            const baseMessage = payload && payload.error && typeof payload.error.message === 'string'
                ? payload.error.message
                : 'Erreur pendant l\'upload.';
            const details = payload && typeof payload.details === 'string' ? payload.details.trim() : '';
            const message = details !== '' ? `${baseMessage} ${details}` : baseMessage;
            throw new Error(message);
        }

        if (payload.data && typeof payload.data.scope === 'string') {
            updateMediaScope(payload.data.scope);
        }

        return payload.data;
    }

    function updateMediaScope(nextScope) {
        const normalizedScope = typeof nextScope === 'string' ? nextScope.trim() : '';
        if (normalizedScope === mediaScope) {
            return;
        }

        mediaScope = normalizedScope;

        const input = ensureMediaScopeInput();
        if (input instanceof HTMLInputElement) {
            input.value = mediaScope;
        }
    }

    function ensureMediaScopeInput() {
        if (mediaScopeInput instanceof HTMLInputElement && mediaScopeInput.name === 'media_scope') {
            return mediaScopeInput;
        }

        const existingNamedInput = form.querySelector('input[name="media_scope"]');
        if (existingNamedInput instanceof HTMLInputElement) {
            mediaScopeInput = existingNamedInput;
            return mediaScopeInput;
        }

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'media_scope';
        input.value = mediaScope;
        form.appendChild(input);
        mediaScopeInput = input;
        return input;
    }

    function resolveMediaScope(input) {
        if (!(input instanceof HTMLInputElement)) {
            return '';
        }

        return input.value.trim();
    }

    function buildMediaListEndpoint(basePath, scope) {
        if (scope === '') {
            return basePath;
        }

        const query = new URLSearchParams({ scope });
        return `${basePath}?${query.toString()}`;
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
        const alt = window.prompt('Texte alternatif (alt) :', fileName) || '';
        const figure = createFigureNode(url, fileName, alt.trim());
        insertBlockAtSelection(figure, { dropX, dropY });
    }

    function createFigureNode(url, fileName, altText) {
        const figure = document.createElement('figure');
        figure.className = 'editor-figure';
        figure.contentEditable = 'false';
        figure.draggable = true;

        const image = document.createElement('img');
        image.src = url;
        image.alt = altText;
        image.className = 'editor-inline-image';
        image.loading = 'lazy';

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'editor-figure-delete';
        deleteButton.textContent = 'Supprimer';
        deleteButton.setAttribute('aria-label', 'Supprimer cette image');
        deleteButton.dataset.editorHelper = 'true';
        deleteButton.contentEditable = 'false';

        deleteButton.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const parent = deleteButton.closest('figure.editor-figure');
            if (!(parent instanceof HTMLElement)) {
                return;
            }

            const nextParagraph = document.createElement('p');
            nextParagraph.appendChild(document.createElement('br'));

            parent.insertAdjacentElement('afterend', nextParagraph);
            parent.remove();

            const selection = window.getSelection();
            if (selection) {
                const range = document.createRange();
                range.setStart(nextParagraph, 0);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
                savedRange = range.cloneRange();
            }

            clearImageResizeOverlay();
            syncHiddenContent();
        });

        const caption = document.createElement('figcaption');
        caption.contentEditable = 'true';
        caption.className = 'editor-figcaption';
        caption.textContent = 'Legende (modifier ce texte)';

        figure.appendChild(deleteButton);
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

    function bindImageResize() {
        editor.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            const image = target.closest('img');
            if (!(image instanceof HTMLImageElement) || !editor.contains(image)) {
                clearImageResizeOverlay();
                return;
            }

            if (!image.classList.contains('editor-inline-image')) {
                clearImageResizeOverlay();
                return;
            }

            showImageResizeOverlay(image);
        });

        editor.addEventListener('keydown', () => {
            clearImageResizeOverlay();
        });

        editor.addEventListener('input', () => {
            updateImageResizeOverlayPosition();
        });

        document.addEventListener('scroll', updateImageResizeOverlayPosition, true);
        window.addEventListener('resize', updateImageResizeOverlayPosition);
        document.addEventListener('pointerdown', (event) => {
            const target = event.target;
            if (!(target instanceof Node)) {
                return;
            }

            if (activeImageResize && activeImageResize.overlay.contains(target)) {
                return;
            }

            if (target instanceof Element && editor.contains(target)) {
                return;
            }

            clearImageResizeOverlay();
        });
    }

    function bindFigureDragMove() {
        editor.addEventListener('dragstart', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            const figure = target.closest('figure.editor-figure');
            if (!(figure instanceof HTMLElement)) {
                draggedFigure = null;
                return;
            }

            draggedFigure = figure;
            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', 'figure-move');
            }
        });

        editor.addEventListener('dragend', () => {
            draggedFigure = null;
        });

        editor.addEventListener('dragover', (event) => {
            if (!(draggedFigure instanceof HTMLElement)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'move';
            }
        });

        editor.addEventListener('drop', (event) => {
            if (!(draggedFigure instanceof HTMLElement) || !editor.contains(draggedFigure)) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            const insertionRange = getInsertionRange(event.clientX, event.clientY);
            if (!(insertionRange instanceof Range)) {
                draggedFigure = null;
                return;
            }

            const parentFigure = insertionRange.startContainer instanceof Element
                ? insertionRange.startContainer.closest('figure.editor-figure')
                : null;

            if (parentFigure instanceof HTMLElement && parentFigure === draggedFigure) {
                draggedFigure = null;
                return;
            }

            const spacer = document.createElement('p');
            spacer.appendChild(document.createElement('br'));

            const fragment = document.createDocumentFragment();
            fragment.appendChild(draggedFigure);
            fragment.appendChild(spacer);

            insertionRange.deleteContents();
            insertionRange.insertNode(fragment);

            const selection = window.getSelection();
            if (selection) {
                const after = document.createRange();
                after.setStart(spacer, 0);
                after.collapse(true);
                selection.removeAllRanges();
                selection.addRange(after);
                savedRange = after.cloneRange();
            }

            draggedFigure = null;
            syncHiddenContent();
            clearImageResizeOverlay();
        });
    }

    function showImageResizeOverlay(image) {
        clearImageResizeOverlay();

        const overlay = document.createElement('div');
        overlay.dataset.role = 'editor-image-resize-overlay';
        overlay.style.position = 'fixed';
        overlay.style.border = '2px solid #2563eb';
        overlay.style.borderRadius = '6px';
        overlay.style.pointerEvents = 'none';
        overlay.style.zIndex = '10000';
        overlay.style.boxSizing = 'border-box';

        const handle = createResizeHandle('e-resize');
        overlay.appendChild(handle);

        document.body.appendChild(overlay);
        activeImageResize = { image, overlay };
        updateImageResizeOverlayPosition();

        handle.addEventListener('pointerdown', (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (!(activeImageResize && activeImageResize.image === image)) {
                return;
            }

            const rect = image.getBoundingClientRect();
            const parentRect = image.parentElement instanceof HTMLElement
                ? image.parentElement.getBoundingClientRect()
                : editor.getBoundingClientRect();

            resizeDragState = {
                pointerId: event.pointerId,
                startX: event.clientX,
                startWidth: rect.width,
                minWidth: 80,
                maxWidth: Math.max(100, parentRect.width),
            };

            image.style.height = 'auto';
            if (handle.setPointerCapture) {
                handle.setPointerCapture(event.pointerId);
            }
        });

        handle.addEventListener('pointermove', (event) => {
            if (!resizeDragState || resizeDragState.pointerId !== event.pointerId) {
                return;
            }

            event.preventDefault();
            const deltaX = event.clientX - resizeDragState.startX;
            const nextWidth = clamp(
                resizeDragState.startWidth + deltaX,
                resizeDragState.minWidth,
                resizeDragState.maxWidth
            );

            image.style.width = `${Math.round(nextWidth)}px`;
            image.style.height = 'auto';
            updateImageResizeOverlayPosition();
        });

        const stopResize = (event) => {
            if (!resizeDragState || resizeDragState.pointerId !== event.pointerId) {
                return;
            }

            if (handle.releasePointerCapture && handle.hasPointerCapture(event.pointerId)) {
                handle.releasePointerCapture(event.pointerId);
            }

            resizeDragState = null;
            updateImageResizeOverlayPosition();
            syncHiddenContent();
        };

        handle.addEventListener('pointerup', stopResize);
        handle.addEventListener('pointercancel', stopResize);
    }

    function createResizeHandle(cursor) {
        const handle = document.createElement('button');
        handle.type = 'button';
        handle.setAttribute('aria-label', 'Redimensionner l\'image');
        handle.style.position = 'absolute';
        handle.style.right = '-7px';
        handle.style.top = '50%';
        handle.style.transform = 'translateY(-50%)';
        handle.style.width = '14px';
        handle.style.height = '14px';
        handle.style.border = '2px solid #ffffff';
        handle.style.background = '#2563eb';
        handle.style.borderRadius = '999px';
        handle.style.padding = '0';
        handle.style.margin = '0';
        handle.style.cursor = cursor;
        handle.style.pointerEvents = 'auto';
        handle.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.35)';
        return handle;
    }

    function updateImageResizeOverlayPosition() {
        if (!activeImageResize) {
            return;
        }

        const image = activeImageResize.image;
        if (!(image instanceof HTMLImageElement) || !editor.contains(image)) {
            clearImageResizeOverlay();
            return;
        }

        const rect = image.getBoundingClientRect();
        if (rect.width <= 0 || rect.height <= 0) {
            clearImageResizeOverlay();
            return;
        }

        const overlay = activeImageResize.overlay;
        overlay.style.left = `${Math.round(rect.left)}px`;
        overlay.style.top = `${Math.round(rect.top)}px`;
        overlay.style.width = `${Math.round(rect.width)}px`;
        overlay.style.height = `${Math.round(rect.height)}px`;
    }

    function clearImageResizeOverlay() {
        resizeDragState = null;

        if (!activeImageResize) {
            return;
        }

        if (activeImageResize.overlay instanceof HTMLElement) {
            activeImageResize.overlay.remove();
        }

        activeImageResize = null;
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

    function insertBlockAtSelection(node, options = {}) {
        if (!(node instanceof Node)) {
            return;
        }

        const range = getInsertionRange(options.dropX, options.dropY);
        if (!(range instanceof Range)) {
            return;
        }

        if (options.preferOutsideTemplate === true) {
            const anchorTemplate = findClosestTemplateAncestor(range.startContainer);
            if (anchorTemplate instanceof HTMLElement && editor.contains(anchorTemplate)) {
                range.setStartAfter(anchorTemplate);
                range.collapse(true);
            }
        }

        const paragraph = document.createElement('p');
        paragraph.appendChild(document.createElement('br'));

        const fragment = document.createDocumentFragment();
        fragment.appendChild(node);
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
            savedRange = after.cloneRange();
        }

        editor.focus();
        syncHiddenContent();
        refreshToolbarState();
    }

    function findClosestTemplateAncestor(node) {
        if (!(node instanceof Node)) {
            return null;
        }

        let current = node instanceof Element ? node : node.parentElement;
        while (current instanceof Element) {
            if (current.classList.contains('editor-template') || current.classList.contains('editor-template-columns')) {
                return current;
            }

            if (current === editor) {
                break;
            }

            current = current.parentElement;
        }

        return null;
    }

    function getInsertionRange(dropX, dropY) {
        editor.focus();

        let range = null;
        if (typeof dropX === 'number' && typeof dropY === 'number') {
            range = getRangeFromPoint(dropX, dropY);
        }

        if (!(range instanceof Range)) {
            restoreSelection();
            range = savedRange instanceof Range ? savedRange.cloneRange() : null;
        }

        if (!(range instanceof Range) || !editor.contains(range.commonAncestorContainer)) {
            range = document.createRange();
            range.selectNodeContents(editor);
            range.collapse(false);
        }

        return range;
    }

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
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
