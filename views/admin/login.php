<?php

declare(strict_types=1);

$error = isset($error) && is_string($error) ? $error : '';
$oldUsername = isset($oldUsername) && is_string($oldUsername) ? $oldUsername : 'admin';
$csrfToken = isset($csrfToken) && is_string($csrfToken) ? $csrfToken : '';

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<section class="admin-login" aria-labelledby="admin-login-title">
    <div class="admin-login__shell">
        <div class="admin-login__visual" aria-hidden="true">
            <div class="admin-login__visual-badge">BackOffice</div>
            <h2 class="admin-login__visual-title">Bienvenue</h2>
            <p class="admin-login__visual-text">Espace securise de gestion editoriale.</p>
            <div class="admin-login__visual-shape admin-login__visual-shape--one"></div>
            <div class="admin-login__visual-shape admin-login__visual-shape--two"></div>
        </div>

        <div class="admin-login__panel">
            <div class="admin-login__card card" style="max-width: 460px; margin: 2rem auto;">
                <div class="card-header admin-login__card-header">
                    <h1 id="admin-login-title" class="mb-1">Connexion BackOffice</h1>
                    <p class="mb-1" style="color: var(--color-text-muted);">
                        Acces reserve a l'administration.
                    </p>
                </div>

                <div class="card-body admin-login__card-body">
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-error" role="alert" aria-live="polite">
                            <?= $escape($error) ?>
                        </div>
                    <?php endif; ?>

                    <form class="admin-login__form" method="post" action="/login" novalidate>
                        <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">

                        <div class="form-group">
                            <label class="form-label" for="username">Nom d'utilisateur</label>
                            <input
                                class="form-control"
                                id="username"
                                name="username"
                                type="text"
                                maxlength="50"
                                required
                                autocomplete="username"
                                value="admin"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password">Mot de passe</label>
                            <input
                                class="form-control"
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                value="admin123"
                            >
                        </div>

                        <div class="text-right admin-login__actions">
                            <button class="btn btn-primary" type="submit">Se connecter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
