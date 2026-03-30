# Documentation technique - Iran War

## 1. Architecture

Application PHP sans framework lourd, structuree en couches simples:

- `public/index.php`: front controller et declaration des routes.
- `src/Core/Router.php`: routeur HTTP (GET/POST/PUT/DELETE, params dynamiques).
- `src/Controllers/Front/*`: pages publiques.
- `src/Controllers/Admin/*`: authentification + gestion back-office.
- `src/Core/Auth.php`: session, login/logout, verification role admin, CSRF token.
- `src/Core/Database.php`: connexion PDO MySQL singleton.
- `src/Models/*`: acces SQL pour `articles`, `categories`, `users`.
- `views/front/*` et `views/admin/*`: rendu HTML.

Flux simplifie:

1. Requete HTTP vers Apache.
2. `public/index.php` charge `config/bootstrap.php`.
3. `Router` resolve la route et execute le controleur.
4. Controleur appelle modele(s) PDO.
5. Vue chargee dans layout front/admin.

## 2. Resume schema BDD

Source: `db/init.sql`.

Tables principales:

- `users`
- `id`, `username`, `email`, `password`, `role`, `created_at`
- `role` est un enum `admin|editor` avec default `editor`
- `categories`
- `id`, `name`, `slug`, `description`, `seo_title`, `seo_description`, `status`, timestamps
- `articles`
- `id`, `category_id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `image`, `image_alt`, `meta_title`, `meta_description`, `status`, `published_at`, timestamps

Relations:

- `articles.category_id` -> `categories.id` (ON DELETE SET NULL)
- `articles.author_id` -> `users.id` (ON DELETE RESTRICT)

Seeds inclus:

- 1 compte admin (`admin` / `admin123`)
- categories initiales (Analyses, Geopolitique, Militaire, Diplomatie, Economie)

## 3. Routes principales

Front:

- `GET /`
- `GET /articles`
- `GET /article/{slug}`
- `GET /categorie/{slug}`
- `GET /a-propos`

Auth/Admin:

- `GET /login`
- `GET /login/`
- `POST /login`
- `POST /login/`
- `POST /logout`
- `GET /admin/dashboard`

Back-office contenu:

- Articles:
- `GET /admin/articles`
- `GET /admin/articles/create`
- `POST /admin/articles`
- `GET /admin/articles/{id}/edit`
- `POST /admin/articles/{id}/update`
- `POST /admin/articles/{id}/delete`
- `POST /admin/articles/{id}/toggle-status`
- Categories:
- `GET /admin/categories`
- `GET /admin/categories/create`
- `POST /admin/categories`
- `GET /admin/categories/{id}/edit`
- `POST /admin/categories/{id}/update`
- `POST /admin/categories/{id}/delete`
- Utilisateurs:
- `GET /admin/users`
- `GET /admin/users/create`
- `POST /admin/users`
- `GET /admin/users/{id}/edit`
- `POST /admin/users/{id}/update`
- `POST /admin/users/{id}/delete`

## 4. SEO implemente (etat actuel)

Elements SEO deja presents:

- Balises dynamiques dans `views/front/layout.php`:
- `<title>`
- `meta description`
- `meta robots`
- Open Graph (`og:title`, `og:description`, `og:type`, `og:url`, `og:image`)
- URL canonique calculee depuis requete courante
- Slugs pour articles et categories (`/article/{slug}`, `/categorie/{slug}`)
- Champs SEO en BDD (`meta_title`, `meta_description`, `seo_title`, `seo_description`)

A completer pour CDC SEO:

- generation `sitemap.xml`
- `robots.txt`
- balisage Schema.org

## 5. Docker compose

Services declares dans `docker-compose.yml`:

- `web`
- build local via `Dockerfile`
- Apache + PHP 8.2
- expose `8080:80`
- volume code source + volume uploads
- `db`
- image `mysql:8.0`
- non expose vers l'hote par defaut (pas de mapping `3306:3306`)
- variables MySQL via `.env`
- volume persistant `db_data`
- montage SQL: `./db/init.sql:/docker-entrypoint-initdb.d/01-init.sql:ro`

Acces base de donnees entre conteneurs:

- depuis `web`, la base est joignable via `db:3306` (hostname Docker = nom du service)
- exemple DSN: `mysql:host=db;port=3306;dbname=<db_name>`

Volumes:

- `db_data`: persistance MySQL
- `uploads_data`: persistance fichiers uploades

Demarrage standard:

```bash
docker compose up -d --build
```

Initialisation DB:

- automatique au premier demarrage seulement (si `db_data` est vide)
- non rejouee si le volume existe deja

Reset complet (recreation volume + reimport auto):

```bash
docker compose down -v && docker compose up -d --build
```

Import manuel (depannage uniquement):

```bash
docker compose exec -T db sh -lc 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < db/init.sql
```

## 6. Checklist Lighthouse (a realiser)

Pages a auditer:

- `http://localhost:8080/`
- `http://localhost:8080/articles`
- `http://localhost:8080/article/<slug-existant>`
- `http://localhost:8080/a-propos`
- `http://localhost:8080/login`

Checklist:

- [ ] Performance >= 80 (mobile et desktop)
- [ ] Accessibility >= 90
- [ ] Best Practices >= 90
- [ ] SEO >= 90
- [ ] Pas d'image sans texte alternatif utile
- [ ] Pas d'erreur console bloquante
- [ ] Title et meta description coherents sur chaque page

Insertion des captures:

- Creer le dossier: `docs/lighthouse/`
- Nommer les captures par page et profil:
- `home-mobile.png`
- `home-desktop.png`
- `articles-mobile.png`
- `article-show-desktop.png`
- `login-mobile.png`

Section a completer ensuite dans ce fichier:

- `## 7. Resultats Lighthouse`
- inserer les images et un court commentaire par page (forces, points a corriger).
