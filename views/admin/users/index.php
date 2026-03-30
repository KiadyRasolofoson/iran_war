<?php

declare(strict_types=1);

$users = is_array($users ?? null) ? $users : [];
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;
$csrfToken = (string) ($csrfToken ?? '');
$currentUser = is_array($currentUser ?? null) ? $currentUser : [];
$isAdmin = strtolower((string) ($currentUser['role'] ?? '')) === 'admin';

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Users</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 0.6rem; text-align: left; }
        .actions { display: flex; gap: 0.5rem; }
        .flash-success { color: #0b6b2a; }
        .flash-error { color: #8a1f11; }
    </style>
</head>
<body>
    <h1>Gestion des utilisateurs</h1>

    <p><a href="/admin/users/create">Creer un utilisateur</a></p>

    <?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
        <p class="flash-success"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (is_string($flashError) && $flashError !== ''): ?>
        <p class="flash-error"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users === []): ?>
                <tr>
                    <td colspan="6">Aucun utilisateur.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int) ($user['id'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string) ($user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($user['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($user['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="actions">
                                <a href="/admin/users/<?= (int) ($user['id'] ?? 0) ?>/edit">Modifier</a>
                                <?php if ($isAdmin): ?>
                                    <form method="post" action="/admin/users/<?= (int) ($user['id'] ?? 0) ?>/delete" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
