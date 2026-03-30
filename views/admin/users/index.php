<?php

declare(strict_types=1);

$users = is_array($users ?? null) ? $users : [];
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;
$csrfToken = (string) ($csrfToken ?? '');
$currentUser = is_array($currentUser ?? null) ? $currentUser : [];
$isAdmin = strtolower((string) ($currentUser['role'] ?? '')) === 'admin';

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<style>
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 0.6rem; text-align: left; }
    .actions { display: flex; gap: 0.5rem; }
    .flash-success { color: #0b6b2a; }
    .flash-error { color: #8a1f11; }
    .btn { display: inline-block; border: 1px solid #333; padding: 8px 12px; border-radius: 6px; background: #fff; color: #111; text-decoration: none; }
</style>

<h1>Gestion des utilisateurs</h1>
<p><a class="btn" href="/admin/users/create">Creer un utilisateur</a></p>

<?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
    <p class="flash-success"><?= $h($flashSuccess) ?></p>
<?php endif; ?>

<?php if (is_string($flashError) && $flashError !== ''): ?>
    <p class="flash-error"><?= $h($flashError) ?></p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($users === []): ?>
            <tr>
                <td colspan="5">Aucun utilisateur.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= (int) ($user['id'] ?? 0) ?></td>
                    <td><?= $h((string) ($user['username'] ?? '')) ?></td>
                    <td><?= $h((string) ($user['email'] ?? '')) ?></td>
                    <td><?= $h((string) ($user['role'] ?? '')) ?></td>
                    <td>
                        <div class="actions">
                            <a href="/admin/users/<?= (int) ($user['id'] ?? 0) ?>/edit">Modifier</a>
                            <?php if ($isAdmin): ?>
                                <form method="post" action="/admin/users/<?= (int) ($user['id'] ?? 0) ?>/delete" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">
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
