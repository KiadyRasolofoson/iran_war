<?php

declare(strict_types=1);

$error = isset($error) && is_string($error) ? $error : '';
$oldUsername = isset($oldUsername) && is_string($oldUsername) ? $oldUsername : '';
$csrfToken = isset($csrfToken) && is_string($csrfToken) ? $csrfToken : '';

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<section aria-labelledby="admin-login-title">
    <div class="card" style="max-width: 460px; margin: 2rem auto;">
        <div class="card-header">
            <h1 id="admin-login-title" class="mb-1">Connexion BackOffice</h1>
            <p class="mb-1" style="color: var(--color-text-muted);">
                Acces reserve a l'administration.
            </p>
        </div>

        <div class="card-body">
            <div class="alert alert-info" role="status">
                Compte par defaut: <code>admin</code> / <code>admin123</code>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error" role="alert" aria-live="polite">
                    <?= $escape($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/login" novalidate>
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
                        value="<?= $escape($oldUsername) ?>"
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
                    >
                </div>

                <div class="text-right">
                    <button class="btn btn-primary" type="submit">Se connecter</button>
                </div>
            </form>
        </div>
    </div>
</section>
