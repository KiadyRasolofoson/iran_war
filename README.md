# Iran War

Application PHP avec front public et back-office admin, executee avec Docker (Apache + PHP 8.2 + MySQL 8).

## Note importante: document root Apache

Le conteneur web est configure pour servir l'application depuis `public/` (`/var/www/html/public` dans le conteneur), et non depuis la racine du projet.

Correction appliquee pour eviter les erreurs Apache 403:

- Le vhost `000-default.conf` pointe explicitement vers `DocumentRoot /var/www/html/public`.
- Le bloc `<Directory /var/www/html/public>` autorise `AllowOverride All` et `Require all granted`.
- `mod_rewrite` est active pour le routage front controller.
- La configuration n'utilise plus de variable Apache litterale non resolue pour le document root.

Consequence pour le routage:

- Le front controller est `public/index.php`.
- Les regles `mod_rewrite` (via `.htaccess`) redirigent les URL applicatives vers ce point d'entree.
- Les URL comme `/login`, `/admin/dashboard` ou `/articles` passent donc toutes par `public/index.php`.

## Demarrage rapide

### 1. Prerequis

- Docker Engine + Docker Compose
- Git

### 2. Cloner et preparer l'environnement

```bash
git clone <url-du-repo>
cd iran_war
cp .env.example .env
```

### 3. Lancer les conteneurs

```bash
docker compose up -d --build
```

### 4. Initialisation SQL automatique

Le fichier `db/init.sql` est monte en lecture seule dans le conteneur MySQL:

- source: `./db/init.sql`
- destination: `/docker-entrypoint-initdb.d/01-init.sql`

Le script est execute automatiquement uniquement au premier demarrage, quand le volume `db_data` est vide.
Si la base existe deja, ce script n'est pas rejoue.

### 5. URLs et acces

- FrontOffice: `http://localhost:8083/`
- BackOffice login: `http://localhost:8083/login`
- BackOffice dashboard: `http://localhost:8083/admin/dashboard`

Identifiants admin par defaut:

- Username: `admin`
- Password: `admin123`

## Base de donnees (reseau Docker)

Par defaut, MySQL n'est pas expose sur votre machine hote (pas de binding local sur `3306`).
Le service web accede a la base via le reseau Docker interne:

- Hostname: `db`
- Port: `3306`
- DSN typique cote web: `mysql:host=db;port=3306;dbname=<db_name>`

### Option: exposer MySQL temporairement pour outils locaux

Si vous voulez connecter un client SQL local (DBeaver, TablePlus, mysql CLI), vous pouvez ajouter temporairement ce mapping dans le service `db` de `docker-compose.yml`:

```yaml
db:
  # ...
  # ports:
  #   - "3307:3306"
```

Puis redemarrer les services:

```bash
docker compose up -d
```

Dans ce cas, votre client local doit se connecter a `127.0.0.1:3307`.

## Commandes utiles

```bash
# Demarrer (avec rebuild)
docker compose up -d --build

# Logs
docker compose logs -f web
docker compose logs -f db

# Etat des conteneurs
docker compose ps

# Arreter
docker compose down

# Reset complet de la base + reinit SQL auto
docker compose down -v && docker compose up -d --build
```

## Depannage

### Import SQL manuel (si necessaire)

Utiliser uniquement si vous devez recharger la base sans supprimer les volumes:

```bash
docker compose exec -T db sh -lc 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < db/init.sql
```

### URLs utiles

- A propos: `http://localhost:8083/a-propos`
- Back articles: `http://localhost:8083/admin/articles`
- Back categories: `http://localhost:8083/admin/categories`
- Back utilisateurs: `http://localhost:8083/admin/users`
