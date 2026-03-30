<?php

declare(strict_types=1);

$categories = is_array($categories ?? null) ? $categories : [];
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$flash = is_array($flash ?? null) ? $flash : [];
$csrfToken = (string) ($csrfToken ?? '');
$mediaScope = (string) ($mediaScope ?? ($old['media_scope'] ?? ''));

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$field = static function (string $key, string $default = '') use ($old): string {
    $value = $old[$key] ?? $default;
    return is_string($value) ? $value : (string) $value;
};

$error = static fn(string $key): string => isset($errors[$key]) ? (string) $errors[$key] : '';
?>
<style>
    /* === Canva-Style Header === */
    .article-editor-topbar {
        position: fixed;
        top: 0;
        left: 280px;
        right: 0;
        height: 64px;
        background: #FFFFFF;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 2rem;
        z-index: 1000;
        backdrop-filter: blur(8px);
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.04);
    }

    .article-editor-topbar h1 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #1A1A1A;
    }

    .article-editor-actions {
        display: flex;
        gap: 0.625rem;
        align-items: center;
    }

    .article-editor-actions .btn {
        height: 40px;
        padding: 0 1.25rem;
        border-radius: 8px;
        font-size: 0.9375rem;
        font-weight: 500;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .article-editor-actions .btn-outline {
        background: transparent;
        color: #1A1A1A;
        border: 1px solid rgba(0, 0, 0, 0.15);
    }

    .article-editor-actions .btn-outline:hover {
        background: rgba(0, 0, 0, 0.04);
        border-color: rgba(0, 0, 0, 0.2);
        transform: none;
        box-shadow: none;
    }

    .article-editor-actions .btn-primary {
        background: linear-gradient(135deg, #BC0000 0%, #9A0000 100%);
        color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(188, 0, 0, 0.25);
    }

    .article-editor-actions .btn-primary:hover {
        box-shadow: 0 4px 12px rgba(188, 0, 0, 0.35);
        transform: translateY(-1px);
    }

    /* Content spacing for fixed header */
    .article-editor-content-wrapper {
        padding-top: 80px;
    }

    @media (max-width: 768px) {
        .article-editor-topbar {
            left: 0;
            padding: 0 1rem;
        }
        .article-editor-topbar h1 {
            font-size: 1rem;
        }
        .article-editor-actions {
            gap: 0.5rem;
        }
        .article-editor-actions .btn {
            padding: 0 0.875rem;
            font-size: 0.875rem;
        }
    }
    .article-editor-alert {
        margin-bottom: 1rem;
        border: 1px solid var(--color-border);
        border-left: 4px solid var(--color-accent);
        border-radius: 12px;
        background: var(--color-bg-card);
        padding: 1rem 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    .article-editor-alert.success { border-left-color: #15803d; }
    .article-editor-alert.error { border-left-color: #b91c1c; }

    .article-editor-layout {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 1.5rem;
    }
    .article-editor-sidebar,
    .article-editor-main { min-width: 0; }
    .article-editor-sidebar .card,
    .article-editor-main .card {
        margin-bottom: 0;
        border-radius: 16px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .article-editor-sidebar .card-header,
    .article-editor-main .card-header {
        background: linear-gradient(180deg, #FAFAFA 0%, #F5F5F5 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
    }

    .article-editor-stack { display: grid; gap: 1.5rem; }
    .article-editor-divider { height: 1px; background: rgba(0, 0, 0, 0.06); margin: 1.5rem 0; border: 0; }
    .article-editor-heading { margin: 0 0 0.75rem; font-size: 1rem; font-weight: 600; color: #1A1A1A; }
    .article-editor-subheading { margin: 0 0 0.625rem; font-size: 0.9375rem; font-weight: 600; color: #1A1A1A; }
    .article-editor-helper { color: #666666; font-size: 0.875rem; line-height: 1.5; }
    .article-editor-error { margin-top: 0.5rem; color: #b91c1c; font-size: 0.875rem; font-weight: 500; }

    /* === Modern WYSIWYG Toolbar (Canva-style) === */
    .wysiwyg-container {
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 16px;
        background: #FFFFFF;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .wysiwyg-toolbar {
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        padding: 1rem 1.25rem;
        display: flex;
        gap: 0.25rem;
        flex-wrap: wrap;
        align-items: center;
        background: linear-gradient(180deg, #FAFAFA 0%, #F8F8F8 100%);
    }

    .wysiwyg-btn {
        background: #FFFFFF;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        padding: 0.5rem 0.875rem;
        cursor: pointer;
        font-size: 0.875rem;
        color: #1A1A1A;
        line-height: 1.2;
        font-weight: 500;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
    }

    .wysiwyg-btn:hover {
        border-color: rgba(0, 0, 0, 0.18);
        background: #F5F5F5;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .wysiwyg-btn.is-active {
        background: linear-gradient(135deg, #BC0000 0%, #9A0000 100%);
        border-color: #BC0000;
        color: #FFFFFF;
        box-shadow: 0 2px 6px rgba(188, 0, 0, 0.3);
    }

    .wysiwyg-btn.is-active:hover {
        box-shadow: 0 3px 8px rgba(188, 0, 0, 0.4);
    }

    .article-editor-toolbar-separator {
        width: 1px;
        height: 32px;
        background: rgba(0, 0, 0, 0.1);
        margin: 0 0.5rem;
    }

    .toolbar-group {
        display: flex;
        gap: 0.25rem;
        align-items: center;
        padding: 0.25rem;
        background: rgba(0, 0, 0, 0.02);
        border-radius: 10px;
    }

    .article-editor-title-wrap { padding: 1.5rem 2rem 0; }
    .article-editor-title {
        width: 100%;
        border: 0;
        border-bottom: 2px solid transparent;
        padding: 0 0 0.75rem;
        margin: 0;
        background: transparent;
        font-size: clamp(1.875rem, 3vw, 2.5rem);
        font-weight: 700;
        line-height: 1.2;
        color: #1A1A1A;
        transition: border-color 0.2s;
        white-space: pre-wrap;
        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-word;
        resize: none;
        overflow: hidden;
        font-family: inherit;
    }
    .article-editor-title:focus-visible {
        outline: none;
        border-bottom-color: #BC0000;
        box-shadow: 0 2px 0 0 rgba(188, 0, 0, 0.1);
    }

    .wysiwyg-editor {
        min-height: 500px;
        padding: 2rem;
        outline: none;
        line-height: 1.75;
        font-size: 1.0625rem;
        color: #1A1A1A;
        background: #FFFFFF;
        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-word;
    }

    .wysiwyg-editor h1,
    .wysiwyg-editor h2,
    .wysiwyg-editor h3,
    .wysiwyg-editor p,
    .wysiwyg-editor blockquote { margin: 0; }
    .wysiwyg-editor.is-drop-target {
        background: linear-gradient(135deg, rgba(188, 0, 0, 0.02) 0%, rgba(188, 0, 0, 0.05) 100%);
        box-shadow: inset 0 0 0 2px rgba(188, 0, 0, 0.3);
    }

    /* Form Controls */
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        background: #FFFFFF;
        color: #1A1A1A;
        font-family: inherit;
        font-size: 0.9375rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 400;
    }

    .form-control:hover {
        border-color: rgba(0, 0, 0, 0.18);
    }

    .form-control:focus {
        outline: none;
        border-color: #BC0000;
        box-shadow: 0 0 0 4px rgba(188, 0, 0, 0.1);
        background: #FFFFFF;
    }

    .form-control:disabled {
        background-color: #F5F5F5;
        color: #999999;
        cursor: not-allowed;
    }

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L6 6L11 1' stroke='%231A1A1A' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 3rem;
    }

    textarea.form-control {
        resize: vertical;
        line-height: 1.6;
    }

    .editor-figure { margin: 1.25rem 0; display: block; position: relative; }
    .editor-inline-image { max-width: 100%; border-radius: var(--radius-md); display: block; margin: 0 auto; }
    .editor-figure-delete {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        border: 0;
        border-radius: 999px;
        padding: 0.3rem 0.55rem;
        font-size: 0.75rem;
        color: #fff;
        background: rgba(185, 28, 28, 0.95);
        cursor: pointer;
    }
    .editor-figcaption {
        margin-top: 0.5rem;
        text-align: center;
        color: var(--color-text-muted);
        font-size: 0.875rem;
        outline: none;
    }
    .editor-figcaption:focus { color: var(--color-text-main); }
    .wysiwyg-editor blockquote,
    .editor-template-quote {
        margin: 1rem 0;
        padding: 0.75rem 0.875rem;
        border-left: 4px solid #1d4ed8;
        background: #eff6ff;
        color: #1e293b;
        font-style: italic;
        border-radius: 6px;
    }

    /* === Modern Image Upload (Canva-style) === */
    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.625rem;
        font-weight: 600;
        font-size: 0.9375rem;
        color: #1A1A1A;
    }

    .form-control[type="file"] {
        display: none;
    }

    .upload-area {
        display: block;
        border: 2px dashed rgba(188, 0, 0, 0.35);
        border-radius: 12px;
        padding: 2rem 1.5rem;
        text-align: center;
        background: linear-gradient(135deg, rgba(188, 0, 0, 0.02) 0%, rgba(188, 0, 0, 0.04) 100%);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .upload-area:hover {
        border-color: rgba(188, 0, 0, 0.6);
        background: linear-gradient(135deg, rgba(188, 0, 0, 0.06) 0%, rgba(188, 0, 0, 0.1) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(188, 0, 0, 0.12);
    }

    .upload-area-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 1rem;
        background: linear-gradient(135deg, #BC0000 0%, #9A0000 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        box-shadow: 0 4px 12px rgba(188, 0, 0, 0.3);
    }

    .upload-area-text {
        font-size: 0.9375rem;
        color: #1A1A1A;
        font-weight: 600;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .upload-area-hint {
        font-size: 0.8125rem;
        color: #666666;
        line-height: 1.4;
    }

    .upload-area.dragover {
        border-color: #BC0000;
        border-style: solid;
        background: linear-gradient(135deg, rgba(188, 0, 0, 0.1) 0%, rgba(188, 0, 0, 0.15) 100%);
        box-shadow: 0 0 0 4px rgba(188, 0, 0, 0.1), 0 4px 16px rgba(188, 0, 0, 0.2);
    }

    .media-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.75rem;
        max-height: 400px;
        overflow-y: auto;
        padding: 0.25rem;
    }

    .media-item {
        background: #FFFFFF;
        border-radius: 12px;
        cursor: pointer;
        border: 2px solid rgba(0, 0, 0, 0.08);
        padding: 0.5rem;
        text-align: center;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .media-item:hover {
        background: #FAFAFA;
        border-color: rgba(188, 0, 0, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .media-item img {
        width: 100%;
        height: 96px;
        object-fit: cover;
        border-radius: 8px;
        display: block;
        border: 1px solid rgba(0, 0, 0, 0.06);
    }

    .media-item-name {
        font-size: 0.75rem;
        color: #1A1A1A;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 0.5rem;
        font-weight: 500;
    }

    .media-empty {
        margin: 2rem 0;
        font-size: 0.875rem;
        color: #666666;
        text-align: center;
    }

    .media-feedback {
        margin-top: 0.75rem;
        font-size: 0.8125rem;
        color: #666666;
        padding: 0.625rem;
        border-radius: 8px;
        background: rgba(0, 0, 0, 0.02);
    }

    .media-feedback[data-level="error"] {
        color: #b91c1c;
        background: rgba(185, 28, 28, 0.08);
    }

    .media-feedback[data-level="success"] {
        color: #166534;
        background: rgba(22, 101, 52, 0.08);
    }

    .article-editor-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }
    .article-editor-meta-full { grid-column: 1 / -1; }

    /* Card Body Padding */
    .card-body {
        padding: 1.75rem 1.5rem;
    }

    /* Improved Editor Figures */
    .editor-figure {
        margin: 1.5rem 0;
        display: block;
        position: relative;
        border-radius: 12px;
        overflow: hidden;
    }

    .editor-inline-image {
        max-width: 100%;
        border-radius: 12px;
        display: block;
        margin: 0 auto;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .editor-figure-delete {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        border: 0;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #FFFFFF;
        background: rgba(185, 28, 28, 0.95);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        backdrop-filter: blur(4px);
    }

    .editor-figure-delete:hover {
        background: rgba(185, 28, 28, 1);
        transform: scale(1.05);
    }

    .editor-figcaption {
        margin-top: 0.75rem;
        text-align: center;
        color: #666666;
        font-size: 0.875rem;
        outline: none;
        padding: 0.5rem;
        font-style: italic;
    }

    .editor-figcaption:focus {
        color: #1A1A1A;
        background: rgba(188, 0, 0, 0.04);
        border-radius: 6px;
    }

    .wysiwyg-editor blockquote,
    .editor-template-quote {
        margin: 1.25rem 0;
        padding: 1rem 1.25rem;
        border-left: 4px solid #BC0000;
        background: linear-gradient(135deg, rgba(188, 0, 0, 0.04) 0%, rgba(188, 0, 0, 0.08) 100%);
        color: #1A1A1A;
        font-style: italic;
        border-radius: 0 12px 12px 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .wysiwyg-container.is-fullscreen {
        position: fixed;
        inset: 0;
        z-index: 9999;
        border-radius: 0;
        border: 0;
        box-shadow: none;
        display: flex;
        flex-direction: column;
        background: #FFFFFF;
    }
    .editor-fullscreen-active { overflow: hidden; }
    .wysiwyg-container.is-fullscreen .wysiwyg-toolbar {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #FFFFFF;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    .wysiwyg-container.is-fullscreen .wysiwyg-editor {
        flex: 1;
        min-height: 0;
        overflow: auto;
        padding-bottom: 120px;
    }

    .editor-template {
        margin: 1.5rem 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .editor-template-hero {
        padding: 2.5rem;
        color: #F8FAFC;
        background: linear-gradient(135deg, #1A1A1A 0%, #BC0000 100%);
    }

    .editor-template-hero h2,
    .editor-template-hero p {
        margin: 0;
        color: inherit;
    }

    .editor-template-hero h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .editor-template-hero p {
        font-size: 1.125rem;
        opacity: 0.95;
    }

    .editor-template-columns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }

    .editor-template-columns > div {
        padding: 1.5rem;
        border: 2px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        background: linear-gradient(135deg, #FFFFFF 0%, #F9F9F9 100%);
        transition: all 0.2s;
    }

    .editor-template-columns > div:hover {
        border-color: rgba(188, 0, 0, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .editor-template-columns h3,
    .editor-template-columns p {
        margin: 0;
    }

    .editor-template-columns h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1A1A1A;
        margin-bottom: 0.75rem;
    }

    .editor-template-columns p {
        color: #666666;
        line-height: 1.6;
    }

    .editor-template blockquote,
    blockquote.editor-template,
    .editor-template-quote {
        margin: 1.25rem 0;
        padding: 1rem 1.25rem;
        border-left: 4px solid #BC0000;
        background: linear-gradient(135deg, rgba(188, 0, 0, 0.04) 0%, rgba(188, 0, 0, 0.08) 100%);
        color: #1A1A1A;
        font-style: italic;
        border-radius: 0 12px 12px 0;
    }
    @media (max-width: 1200px) {
        .article-editor-layout {
            grid-template-columns: 1fr;
        }
        .article-editor-sidebar {
            order: 2;
        }
        .article-editor-main {
            order: 1;
        }
    }

    @media (max-width: 900px) {
        .editor-template-columns { grid-template-columns: 1fr; }
        .article-editor-meta-grid { grid-template-columns: 1fr; }
        .toolbar-group {
            padding: 0;
            background: transparent;
        }
        .wysiwyg-toolbar {
            padding: 0.875rem 1rem;
            gap: 0.375rem;
        }
        .wysiwyg-btn {
            min-width: 32px;
            height: 32px;
            padding: 0.375rem 0.625rem;
            font-size: 0.8125rem;
        }
    }

    @media (max-width: 640px) {
        .article-editor-topbar {
            height: auto;
            min-height: 64px;
            padding: 0.75rem 1rem;
            flex-wrap: wrap;
        }
        .article-editor-actions {
            width: 100%;
            justify-content: flex-end;
        }
        .article-editor-actions .btn span {
            display: none;
        }
        .article-editor-actions .btn {
            padding: 0 0.75rem;
        }
        .article-editor-content-wrapper {
            padding-top: 100px;
        }
    }
</style>

<div class="article-editor-topbar">
    <h1>Créer un article</h1>
    <div class="article-editor-actions">
        <a class="btn btn-outline" href="/admin/articles">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Retour
        </a>
        <button type="submit" form="article-form" class="btn btn-outline" formaction="/admin/articles/preview" formtarget="_blank">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
            Aperçu
        </button>
        <button type="submit" form="article-form" class="btn btn-primary" name="submit_action" value="publish_and_view">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"></path>
                <path d="M12 5l7 7-7 7"></path>
            </svg>
            Publier et voir
        </button>
        <button type="submit" form="article-form" class="btn btn-primary" data-hook="save-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Enregistrer
        </button>
    </div>
</div>

<?php foreach ($flash as $message): ?>
    <div class="article-editor-alert <?= $h((string) ($message['type'] ?? '')) ?>" role="status" aria-live="polite">
        <?= $h((string) ($message['message'] ?? '')) ?>
    </div>
<?php endforeach; ?>

<div class="article-editor-content-wrapper">
    <form id="article-form" method="post" action="/admin/articles" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">
    <input type="hidden" name="media_scope" value="<?= $h($mediaScope) ?>">

    <div class="article-editor-layout">
        <aside class="article-editor-sidebar" data-hook="media-sidebar" aria-label="Panneau media">
            <section class="card">
                <div class="card-header">Médias</div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label" for="image">Image principale</label>
                        <label for="image" class="upload-area">
                            <div class="upload-area-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            </div>
                            <div class="upload-area-text">Cliquez pour télécharger une image</div>
                            <div class="upload-area-hint">ou glissez-déposez (JPG, PNG, WEBP, GIF)</div>
                        </label>
                        <input class="form-control" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" data-hook="main-image-upload">
                        <?php if ($error('image') !== ''): ?><div class="article-editor-error"><?= $h($error('image')) ?></div><?php endif; ?>
                    </div>

                    <hr class="article-editor-divider">

                    <h3 class="article-editor-subheading">Bibliothèque d'images</h3>
                    <p class="article-editor-helper mb-1">Cliquez ou glissez-déposez dans l'éditeur.</p>
                    <div class="form-group">
                        <label for="inline-media-upload" class="upload-area">
                            <div class="upload-area-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <div class="upload-area-text">Ajouter à la bibliothèque</div>
                            <div class="upload-area-hint">Images pour insertion dans l'article</div>
                        </label>
                        <input class="form-control" id="inline-media-upload" type="file" data-hook="inline-media-upload" accept=".jpg,.jpeg,.png,.webp,.gif">
                    </div>
                    <div class="media-list" data-hook="media-list">
                        <p class="media-empty">Chargement...</p>
                    </div>
                    <p class="media-feedback" data-hook="media-feedback" data-level="info" aria-live="polite"></p>
                </div>
            </section>
        </aside>

        <main class="article-editor-main">
            <div class="article-editor-stack">
                <section class="card" aria-labelledby="editor-section-title">
                    <div class="card-header" id="editor-section-title">Contenu</div>
                    <div class="card-body">
                        <div class="wysiwyg-container" data-hook="wysiwyg-container">
                            <div class="wysiwyg-toolbar" data-hook="wysiwyg-toolbar">
                                <!-- Text Formatting Group -->
                                <div class="toolbar-group">
                                    <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="P" title="Paragraphe">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="4" y1="7" x2="20" y2="7"></line>
                                            <line x1="4" y1="12" x2="20" y2="12"></line>
                                            <line x1="4" y1="17" x2="12" y2="17"></line>
                                        </svg>
                                    </button>
                                    <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="H1" title="Titre 1">H1</button>
                                    <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="H2" title="Titre 2">H2</button>
                                    <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="H3" title="Titre 3">H3</button>
                                </div>

                                <span class="article-editor-toolbar-separator" aria-hidden="true"></span>

                                <!-- Text Style Group -->
                                <div class="toolbar-group">
                                    <button type="button" class="wysiwyg-btn" data-action="bold" title="Gras">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6zM6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" class="wysiwyg-btn" data-action="italic" title="Italique">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="19" y1="4" x2="10" y2="4"></line>
                                            <line x1="14" y1="20" x2="5" y2="20"></line>
                                            <line x1="15" y1="4" x2="9" y2="20"></line>
                                        </svg>
                                    </button>
                                    <button type="button" class="wysiwyg-btn" data-action="underline" title="Souligné">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3"></path>
                                            <line x1="4" y1="21" x2="20" y2="21"></line>
                                        </svg>
                                    </button>
                                </div>

                                <span class="article-editor-toolbar-separator" aria-hidden="true"></span>

                                <!-- Lists & Links Group -->
                                <div class="toolbar-group">
                                    <button type="button" class="wysiwyg-btn" data-action="insertUnorderedList" title="Liste à puces">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="8" y1="6" x2="21" y2="6"></line>
                                            <line x1="8" y1="12" x2="21" y2="12"></line>
                                            <line x1="8" y1="18" x2="21" y2="18"></line>
                                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                        </svg>
                                    </button>
                                    <button type="button" class="wysiwyg-btn" data-action="insertOrderedList" title="Liste numérotée">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="10" y1="6" x2="21" y2="6"></line>
                                            <line x1="10" y1="12" x2="21" y2="12"></line>
                                            <line x1="10" y1="18" x2="21" y2="18"></line>
                                            <path d="M4 6h1v4"></path>
                                            <path d="M4 10h2"></path>
                                            <path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"></path>
                                        </svg>
                                    </button>
                                    <button type="button" class="wysiwyg-btn" data-action="createLink" title="Insérer un lien">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                        </svg>
                                    </button>
                                    <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="BLOCKQUOTE" title="Citation">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"></path>
                                            <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <span class="article-editor-toolbar-separator" aria-hidden="true"></span>

                                <!-- Alignment Group -->
                                <div class="toolbar-group">
                                    <button type="button" class="wysiwyg-btn" data-action="justifyLeft" title="Aligner à gauche">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="17" y1="10" x2="3" y2="10"></line>
                                            <line x1="21" y1="6" x2="3" y2="6"></line>
                                            <line x1="21" y1="14" x2="3" y2="14"></line>
                                            <line x1="17" y1="18" x2="3" y2="18"></line>
                                        </svg>
                                    </button>
                                    <button type="button" class="wysiwyg-btn" data-action="justifyCenter" title="Centrer">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="10" x2="6" y2="10"></line>
                                            <line x1="21" y1="6" x2="3" y2="6"></line>
                                            <line x1="21" y1="14" x2="3" y2="14"></line>
                                            <line x1="18" y1="18" x2="6" y2="18"></line>
                                        </svg>
                                    </button>
                                    <button type="button" class="wysiwyg-btn" data-action="justifyRight" title="Aligner à droite">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="21" y1="10" x2="7" y2="10"></line>
                                            <line x1="21" y1="6" x2="3" y2="6"></line>
                                            <line x1="21" y1="14" x2="3" y2="14"></line>
                                            <line x1="21" y1="18" x2="7" y2="18"></line>
                                        </svg>
                                    </button>
                                </div>

                                <span class="article-editor-toolbar-separator" aria-hidden="true"></span>

                                <!-- Templates Group -->
                                <div class="toolbar-group">
                                    <button type="button" class="wysiwyg-btn" data-action="insert-template" data-template="hero" title="Insérer Hero">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                            <path d="M16 3l-4 4-4-4"></path>
                                        </svg>
                                    </button>
                                    <button type="button" class="wysiwyg-btn" data-action="insert-template" data-template="columns" title="2 Colonnes">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="7" height="18" rx="1"></rect>
                                            <rect x="14" y="3" width="7" height="18" rx="1"></rect>
                                        </svg>
                                    </button>
                                    <button type="button" class="wysiwyg-btn" data-action="insert-template" data-template="quote" title="Citation bloc">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <span style="flex-grow: 1;"></span>

                                <!-- View Options -->
                                <button type="button" class="wysiwyg-btn" data-action="toggle-fullscreen" title="Mode plein écran">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="article-editor-title-wrap">
                                <label class="form-label" for="title">Titre</label>
                                <textarea
                                    id="title"
                                    class="article-editor-title"
                                    name="title"
                                    rows="1"
                                    required
                                    maxlength="255"
                                    placeholder="Titre de l'article..."
                                    <?= $error('title') !== '' ? 'aria-invalid="true"' : '' ?>
                                ><?= $h($field('title')) ?></textarea>
                                <?php if ($error('title') !== ''): ?><div class="article-editor-error"><?= $h($error('title')) ?></div><?php endif; ?>
                            </div>

                            <div class="wysiwyg-editor" contenteditable="true" data-hook="visual-editor" placeholder="Commencez a ecrire votre article ici..."></div>

                            <textarea id="content" name="content" style="display: none;" required data-hook="content-hidden"><?= $h($field('content')) ?></textarea>
                            <?php if ($error('content') !== ''): ?><div class="article-editor-error" style="padding: 0 1.5rem 1rem;"><?= $h($error('content')) ?></div><?php endif; ?>
                        </div>
                    </div>
                </section>

                <section class="card" aria-labelledby="meta-section-title">
                    <div class="card-header" id="meta-section-title">Informations et SEO</div>
                    <div class="card-body">
                        <div class="article-editor-meta-grid">
                            <div class="form-group">
                                <label class="form-label" for="slug">Slug (auto si vide)</label>
                                <input class="form-control" id="slug" name="slug" type="text" value="<?= $h($field('slug')) ?>" maxlength="255" placeholder="mon-article" <?= $error('slug') !== '' ? 'aria-invalid="true"' : '' ?>>
                                <?php if ($error('slug') !== ''): ?><div class="article-editor-error"><?= $h($error('slug')) ?></div><?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="category_id">Categorie</label>
                                <select class="form-control" id="category_id" name="category_id" <?= $error('category_id') !== '' ? 'aria-invalid="true"' : '' ?>>
                                    <option value="">Aucune</option>
                                    <?php $categoryId = $field('category_id'); ?>
                                    <?php foreach ($categories as $category): ?>
                                        <?php $id = (string) ($category['id'] ?? ''); ?>
                                        <option value="<?= $h($id) ?>" <?= $categoryId === $id ? 'selected' : '' ?>>
                                            <?= $h((string) ($category['name'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($error('category_id') !== ''): ?><div class="article-editor-error"><?= $h($error('category_id')) ?></div><?php endif; ?>
                            </div>

                            <div class="form-group article-editor-meta-full">
                                <label class="form-label" for="excerpt">Extrait (visible sur les listes)</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?= $h($field('excerpt')) ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="status">Statut</label>
                                <select class="form-control" id="status" name="status" <?= $error('status') !== '' ? 'aria-invalid="true"' : '' ?>>
                                    <?php $status = $field('status', 'draft'); ?>
                                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                                </select>
                                <?php if ($error('status') !== ''): ?><div class="article-editor-error"><?= $h($error('status')) ?></div><?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="published_at">Date de publication</label>
                                <input class="form-control" id="published_at" name="published_at" type="datetime-local" value="<?= $h($field('published_at')) ?>" <?= $error('published_at') !== '' ? 'aria-invalid="true"' : '' ?>>
                                <?php if ($error('published_at') !== ''): ?><div class="article-editor-error"><?= $h($error('published_at')) ?></div><?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="image_alt">Texte alternatif de l'image (SEO)</label>
                                <input class="form-control" id="image_alt" name="image_alt" type="text" value="<?= $h($field('image_alt')) ?>" maxlength="255" <?= $error('image_alt') !== '' ? 'aria-invalid="true"' : '' ?>>
                                <?php if ($error('image_alt') !== ''): ?><div class="article-editor-error"><?= $h($error('image_alt')) ?></div><?php endif; ?>
                            </div>

                            <div class="article-editor-meta-full"><hr class="article-editor-divider"></div>

                            <div class="form-group article-editor-meta-full">
                                <label class="form-label" for="meta_title">Meta Title</label>
                                <input class="form-control" id="meta_title" name="meta_title" type="text" value="<?= $h($field('meta_title')) ?>" maxlength="70" <?= $error('meta_title') !== '' ? 'aria-invalid="true"' : '' ?>>
                                <?php if ($error('meta_title') !== ''): ?><div class="article-editor-error"><?= $h($error('meta_title')) ?></div><?php endif; ?>
                            </div>

                            <div class="form-group article-editor-meta-full">
                                <label class="form-label" for="meta_description">Meta Description</label>
                                <textarea class="form-control" id="meta_description" name="meta_description" rows="2" maxlength="160" <?= $error('meta_description') !== '' ? 'aria-invalid="true"' : '' ?>><?= $h($field('meta_description')) ?></textarea>
                                <?php if ($error('meta_description') !== ''): ?><div class="article-editor-error"><?= $h($error('meta_description')) ?></div><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</form>
</div>

<script src="/assets/js/admin-article-editor.js" defer></script>
<script>
// Auto-resize title textarea
document.addEventListener('DOMContentLoaded', function() {
    const titleTextarea = document.getElementById('title');

    if (titleTextarea) {
        function autoResize() {
            titleTextarea.style.height = 'auto';
            titleTextarea.style.height = titleTextarea.scrollHeight + 'px';
        }

        titleTextarea.addEventListener('input', autoResize);
        // Initial resize
        autoResize();
    }

    // Enhance upload areas with drag & drop visual feedback
    const uploadAreas = document.querySelectorAll('.upload-area');

    uploadAreas.forEach(function(area) {
        ['dragenter', 'dragover'].forEach(function(eventName) {
            area.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                area.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function(eventName) {
            area.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                area.classList.remove('dragover');
            });
        });

        // Handle file drop
        area.addEventListener('drop', function(e) {
            const input = document.getElementById(area.getAttribute('for'));
            if (input && e.dataTransfer && e.dataTransfer.files.length > 0) {
                input.files = e.dataTransfer.files;
                // Trigger change event
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        });
    });
});
</script>
