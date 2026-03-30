<?php

declare(strict_types=1);

$error = isset($error) && is_string($error) ? $error : '';
$oldUsername = isset($oldUsername) && is_string($oldUsername) ? $oldUsername : '';
$csrfToken = isset($csrfToken) && is_string($csrfToken) ? $csrfToken : '';

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<h1>Connexion BackOffice</h1>
<p style="color:#4b5563;">Compte par defaut: <code>admin</code> / <code>admin123</code></p>

<?php if ($error !== ''): ?>
    <p style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px;border-radius:6px;">
        <?= $escape($error) ?>
    </p>
<?php endif; ?>

<form method="post" action="/login" novalidate>
    <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">

    <p>
        <label for="username">Nom d'utilisateur</label><br>
        <input id="username" name="username" type="text" maxlength="50" required value="<?= $escape($oldUsername) ?>">
    </p>

    <p>
        <label for="password">Mot de passe</label><br>
        <input id="password" name="password" type="password" required>
    </p>

    <p>
        <button type="submit">Se connecter</button>
    </p>
</form>
