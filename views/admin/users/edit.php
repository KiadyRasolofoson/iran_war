<?php

declare(strict_types=1);

$errors = is_array($errors ?? null) ? $errors : [];
$user = is_array($user ?? null) ? $user : [];
$csrfToken = (string) ($csrfToken ?? '');
$currentUser = is_array($currentUser ?? null) ? $currentUser : [];
$isAdmin = strtolower((string) ($currentUser['role'] ?? '')) === 'admin';

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editer Utilisateur</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; max-width: 900px; }
        label { display: block; margin-top: 0.8rem; }
        input, select { width: 100%; padding: 0.5rem; }
        .errors { color: #8a1f11; }
        .actions { margin-top: 1rem; display: flex; gap: 0.5rem; }
    </style>
</head>
<body>
    <h1>Modifier un utilisateur</h1>

    <p><a href="/admin/users">Retour a la liste</a></p>

    <?php if ($errors !== []): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin/users/<?= (int) ($user['id'] ?? 0) ?>/update">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <label for="username">Username</label>
        <input id="username" name="username" type="text" required value="<?= htmlspecialchars((string) ($user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <label for="password">Nouveau mot de passe (laisser vide pour conserver)</label>
        <input id="password" name="password" type="password">

        <label for="role">Role</label>
        <select id="role" name="role" <?= $isAdmin ? '' : 'disabled' ?>>
            <?php if ($isAdmin): ?>
                <option value="admin" <?= (($user['role'] ?? '') === 'admin') ? 'selected' : '' ?>>admin</option>
            <?php endif; ?>
            <option value="editor" <?= (($user['role'] ?? '') === 'editor') ? 'selected' : '' ?>>editor</option>
        </select>
        <?php if (!$isAdmin): ?>
            <input type="hidden" name="role" value="<?= htmlspecialchars((string) ($user['role'] ?? 'editor'), ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="active" <?= (($user['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>active</option>
            <option value="disabled" <?= (($user['status'] ?? '') === 'disabled') ? 'selected' : '' ?>>disabled</option>
        </select>

        <div class="actions">
            <button type="submit">Enregistrer</button>
        </div>
    </form>
</body>
</html>
