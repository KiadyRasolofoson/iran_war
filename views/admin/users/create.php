<?php

declare(strict_types=1);

$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$csrfToken = (string) ($csrfToken ?? '');
$currentUser = is_array($currentUser ?? null) ? $currentUser : [];
$isAdmin = strtolower((string) ($currentUser['role'] ?? '')) === 'admin';

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<style>
    label { display: block; margin-top: 0.8rem; }
    input, select { width: 100%; padding: 0.5rem; }
    .errors { color: #8a1f11; }
    .actions { margin-top: 1rem; display: flex; gap: 0.5rem; }
    .btn { display: inline-block; border: 1px solid #333; padding: 8px 12px; border-radius: 6px; background: #fff; color: #111; text-decoration: none; }
</style>

<h1>Creer un utilisateur</h1>
<p><a class="btn" href="/admin/users">Retour a la liste</a></p>

<?php if ($errors !== []): ?>
    <div class="errors">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $h((string) $error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="/admin/users">
    <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">

    <label for="username">Username</label>
    <input id="username" name="username" type="text" required value="<?= $h((string) ($old['username'] ?? '')) ?>">

    <label for="email">Email</label>
    <input id="email" name="email" type="email" required value="<?= $h((string) ($old['email'] ?? '')) ?>">

    <label for="password">Mot de passe</label>
    <input id="password" name="password" type="password" required>

    <label for="role">Role</label>
    <select id="role" name="role">
        <?php if ($isAdmin): ?>
            <option value="admin" <?= (($old['role'] ?? '') === 'admin') ? 'selected' : '' ?>>admin</option>
        <?php endif; ?>
        <option value="editor" <?= (($old['role'] ?? 'editor') === 'editor') ? 'selected' : '' ?>>editor</option>
    </select>

    <div class="actions">
        <button type="submit">Enregistrer</button>
    </div>
</form>
