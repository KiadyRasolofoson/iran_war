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

<div style="display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; margin-bottom: 24px;">
    <div>
        <h1 class="mb-1" style="margin-top: 0;">Gestion des utilisateurs</h1>
        <p class="mb-1" style="margin-top: 0; color: var(--color-text-muted);">Administration des comptes et des roles BackOffice.</p>
    </div>
    <a class="btn btn-primary" href="/admin/users/create">Creer un utilisateur</a>
</div>

<?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
    <div class="alert alert-success"><?= $h($flashSuccess) ?></div>
<?php endif; ?>

<?php if (is_string($flashError) && $flashError !== ''): ?>
    <div class="alert alert-error"><?= $h($flashError) ?></div>
<?php endif; ?>

<section class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="gap: 0.75rem; flex-wrap: wrap;">
        <span>Liste des utilisateurs</span>
        <span class="badge badge-neutral"><?= count($users) ?> comptes</span>
    </div>

    <div class="card-body">
        <?php if ($users === []): ?>
            <div class="alert alert-info mb-1">Aucun utilisateur.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
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
                        <?php foreach ($users as $user): ?>
                            <?php
                            $userId = (int) ($user['id'] ?? 0);
                            $userRole = strtolower((string) ($user['role'] ?? ''));
                            $roleClass = $userRole === 'admin' ? 'badge-primary' : 'badge-neutral';
                            ?>
                            <tr>
                                <td><?= $userId ?></td>
                                <td><?= $h((string) ($user['username'] ?? '')) ?></td>
                                <td><?= $h((string) ($user['email'] ?? '')) ?></td>
                                <td><span class="badge <?= $roleClass ?>"><?= $h((string) ($user['role'] ?? '')) ?></span></td>
                                <td>
                                    <div class="d-flex gap-2 align-items-center" style="flex-wrap: wrap;">
                                        <a class="btn btn-outline" href="/admin/users/<?= $userId ?>/edit">Modifier</a>
                                        <?php if ($isAdmin): ?>
                                            <form method="post" action="/admin/users/<?= $userId ?>/delete" onsubmit="return confirm('Supprimer cet utilisateur ?');" style="margin: 0;">
                                                <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">
                                                <button class="btn btn-outline" style="color: var(--color-accent); border-color: var(--color-accent);" type="submit">Supprimer</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
