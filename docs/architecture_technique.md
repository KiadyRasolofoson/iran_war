# Architecture Technique - Mini Projet Laravel Iran War

## 1. Objectif technique

Le projet vise un site Laravel avec deux zones:
- Frontoffice public pour la consultation des contenus.
- Backoffice admin pour la gestion editoriale.

Le socle technique privilegie des composants Laravel standards, explicites et facilement maintenables.

## 2. Architecture applicative Laravel

### 2.1 Couches principales

- `routes/web.php`: declaration des routes web front et admin.
- `app/Http/Controllers/Front/*`: controllers frontoffice.
- `app/Http/Controllers/Admin/*`: controllers backoffice.
- `app/Http/Requests/*`: validations formulaire via `FormRequest`.
- `app/Models/*`: modeles Eloquent.
- `resources/views/front/*`: vues Blade frontoffice.
- `resources/views/admin/*`: vues Blade backoffice.

### 2.2 Conventions de routage

- Prefixe de noms front: `front.*`.
- Prefixe de noms admin: `admin.*`.
- Prefixe URL admin: `/admin`.
- URLs publiques basees sur des slugs (`kebab-case`) plutot que des IDs internes.

## 3. Architecture BDD (cadrage)

Schema initial recommande:
- `users`: comptes utilisateurs (dont admin).
- `articles`: contenu principal (titre, slug, resume, contenu, statut, dates).
- `categories`: regroupement des articles.
- `article_category`: table pivot many-to-many.

Contraintes recommandees:
- Index unique sur `articles.slug`.
- Index unique sur `categories.slug`.
- Contraintes de cle etrangere sur la pivot.
- Champs de publication (`published_at`, `is_published`) selon besoin metier.

## 4. Routage fonctionnel cible

### 4.1 Frontoffice

- `GET /` -> `front.home`
- `GET /articles` -> `front.articles.index`
- `GET /articles/{slug}` -> `front.articles.show`
- `GET /contact` -> `front.contact`
- `GET /a-propos` -> `front.about`

### 4.2 Backoffice

- `GET /admin` -> `admin.dashboard`
- `Route::resource('/admin/articles', ...)` -> `admin.articles.*`
- `Route::resource('/admin/categories', ...)` -> `admin.categories.*`

Toutes les routes admin doivent etre protegees (auth + role admin selon implementation).

## 5. Conventions SEO

Pour chaque page publique indexable:
- `title` unique et descriptif.
- `meta description` concise et pertinente.
- Balise `link rel="canonical"` coherent avec l'URL canonique.
- Un seul `h1`, puis hierarchie logique (`h2`, `h3`).

Recommandations complementaires:
- `robots.txt` present a la racine publique.
- `sitemap.xml` genere et reference dans `robots.txt`.
- Slugs stables pour limiter les ruptures d'indexation.

## 6. Docker et execution locale

Pile cible locale:
- Laravel Sail (PHP + Nginx + MySQL).
- Services optionnels: Redis, Mailpit.

Cycle minimal attendu:
1. Demarrage conteneurs.
2. Installation dependances.
3. Migration base.
4. Lancement tests.

## 7. Plan de tests

### 7.1 Tests feature frontoffice

- Verifier codes HTTP 200 sur pages publiques.
- Verifier rendu des elements SEO critiques (`title`, canonical).
- Verifier resolution des pages detail via slug valide/invalide.

### 7.2 Tests feature backoffice

- Verifier redirection vers login si non authentifie.
- Verifier acces admin authentifie.
- Verifier scenarios CRUD nominaux (create/update/delete).

### 7.3 Tests de non-regression SEO

- Presence de `title` non vide.
- Presence de `meta description` non vide.
- Presence de canonical sur pages indexables.

## 8. Livrables de cadrage

Les documents de reference minimaux du projet sont:
- `README.md`
- `TODO.md`
- `.github/copilot-instructions.md`
- `.github/skills/iran-war-laravel/SKILL.md`
