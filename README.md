# Iran War - Mini Projet Laravel

Mini-projet Laravel structure autour d'un frontoffice public et d'un backoffice admin.

## 1. Perimetre

- Frontoffice: pages publiques de consultation.
- Backoffice: administration des contenus.
- SEO: bonnes pratiques techniques de base.
- Execution locale: Docker via Laravel Sail.

## 2. Prerequis

- Docker Desktop (ou Docker Engine + Docker Compose)
- Git
- (Optionnel) `make`, `curl`

## 3. Installation (Docker / Sail)

### Option A - Creer un projet Laravel neuf avec Sail

```bash
curl -s "https://laravel.build/iran-war" | bash
cd iran-war
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

### Option B - Depuis un projet Laravel deja initialise

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

## 4. Commandes utiles

```bash
./vendor/bin/sail up -d
./vendor/bin/sail down
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan test
```

## 5. URLs locales (cibles)

- Application: `http://localhost`
- Backoffice: `http://localhost/admin`
- Mailpit (si active): `http://localhost:8025`

## 6. Identifiants par defaut (placeholder)

- Login admin: `admin`
- Mot de passe admin: `admin123`

Important: ces identifiants sont des placeholders de developpement et doivent etre modifies avant toute mise en ligne.

## 7. Structure documentaire

- Skill projet: `.github/skills/iran-war-laravel/SKILL.md`
- Conventions Copilot: `.github/copilot-instructions.md`
- Architecture: `docs/architecture_technique.md`
- Suivi des taches: `TODO.md`

## 8. Etat d'avancement

Consulter `TODO.md` pour le detail des elements faits, en cours et a faire.
