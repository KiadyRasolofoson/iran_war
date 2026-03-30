<?php

declare(strict_types=1);

$errors = is_array($errors ?? null) ? $errors : [];
$user = is_array($user ?? null) ? $user : [];
$csrfToken = (string) ($csrfToken ?? '');
$currentUser = is_array($currentUser ?? null) ? $currentUser : [];
$isAdmin = strtolower((string) ($currentUser['role'] ?? '')) === 'admin';

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="d-flex justify-content-between align-items-center mb-2" style="gap: 0.75rem; flex-wrap: wrap;">
    <h1 class="mb-1" style="margin: 0;">Modifier un utilisateur</h1>
    <a class="btn btn-outline" href="/admin/users">Retour a la liste</a>
</div>

<?php if ($errors !== []): ?>
    <div class="alert alert-info" role="alert" aria-live="assertive" id="user-form-errors">
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $h((string) $error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="/admin/users/<?= (int) ($user['id'] ?? 0) ?>/update" class="card" <?= $errors !== [] ? 'aria-describedby="user-form-errors"' : '' ?>>
    <div class="card-header">Informations utilisateur</div>
    <div class="card-body">
        <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">

        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input class="form-control" id="username" name="username" type="text" required autocomplete="username" value="<?= $h((string) ($user['username'] ?? '')) ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" id="email" name="email" type="email" required autocomplete="email" value="<?= $h((string) ($user['email'] ?? '')) ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Nouveau mot de passe (laisser vide pour conserver)</label>
            <input class="form-control" id="password" name="password" type="password" autocomplete="new-password">
        </div>

        <div class="form-group">
            <label class="form-label" for="role">Role</label>
            <select class="form-control" id="role" name="role" <?= $isAdmin ? '' : 'disabled' ?>>
                <?php if ($isAdmin): ?>
                    <option value="admin" <?= (($user['role'] ?? '') === 'admin') ? 'selected' : '' ?>>admin</option>
                <?php endif; ?>
                <option value="editor" <?= (($user['role'] ?? '') === 'editor') ? 'selected' : '' ?>>editor</option>
            </select>
            <?php if (!$isAdmin): ?>
                <input type="hidden" name="role" value="<?= $h((string) ($user['role'] ?? 'editor')) ?>">
            <?php endif; ?>
        </div>

        <div class="d-flex gap-2 mt-3" style="flex-wrap: wrap;">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a class="btn btn-outline" href="/admin/users">Annuler</a>
        </div>
    </div>
</form>
