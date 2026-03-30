---
name: iran-war-laravel
description: Skill projet pour construire un mini-site Laravel avec frontoffice, backoffice, SEO, Docker, tests et livrables de documentation.
---

# Skill Projet: Iran War Laravel

## Objectif

Construire un mini-projet Laravel structuré, maintenable et livrable, avec:
- un frontoffice public,
- un backoffice administrable,
- des fondamentaux SEO techniques,
- une exécution locale via Docker (Laravel Sail),
- une base de tests automatisés,
- des livrables de documentation à jour.

## Contexte d'execution

- Langue de travail: francais
- Framework: Laravel (version courante du projet)
- Rendu front: Blade
- Base de donnees: MySQL via Docker
- Qualite: tests feature prioritaires + conventions de nommage stables

## Workflow actionnable

Suivre les phases dans l'ordre. Ne pas passer a la phase suivante sans livrable minimal de la phase courante.

### 1. Frontoffice

1. Definir les pages publiques cibles (accueil, liste, detail, contact, a propos, mentions).
2. Creer les routes front dans `routes/web.php` avec noms explicites (`front.*`).
3. Creer les controllers dedies (`app/Http/Controllers/Front/*Controller.php`).
4. Creer les vues Blade dans `resources/views/front/` avec layouts partages.
5. Integrer slugs lisibles dans les URLs publiques.
6. Verifier accessibilite minimale: titres de pages, navigation, liens actifs.

### 2. Backoffice

1. Segmenter les routes admin sous prefixe `/admin` et noms `admin.*`.
2. Proteger les routes admin par middleware d'authentification.
3. Creer controllers backoffice (`app/Http/Controllers/Admin/*Controller.php`).
4. Implementer CRUD des contenus critiques (ex: articles, categories).
5. Ajouter validations de formulaires via `FormRequest`.
6. Journaliser les actions sensibles (creation, suppression, publication).
7. Ajouter pagination et filtres de base sur les listes admin.

### 3. SEO technique

1. Generer des balises `title` et `meta description` dynamiques par page.
2. Forcer un slug unique et stable pour les contenus indexables.
3. Ajouter canonical sur les pages indexables.
4. Prevoir `robots.txt` et `sitemap.xml`.
5. Verifier structure semantique H1/H2 et liens internes.

### 4. Docker / Environnement

1. Initialiser le projet avec Sail ou aligner la configuration Docker existante.
2. Documenter le demarrage (`up`, `migrate`, `seed`) dans le README.
3. Definir variables `.env` minimales et non sensibles.
4. Verifier l'acces local (`http://localhost`) et la connectivite BDD.

### 5. Tests

1. Ecrire des tests feature frontoffice (200 OK, rendu attendu).
2. Ecrire des tests feature backoffice (auth requise, CRUD nominal).
3. Ajouter tests SEO de base (presence `title`, canonical, noindex si besoin).
4. Integrer une commande unique d'execution des tests dans la doc.

### 6. Livrables

1. Mettre a jour `README.md` (installation, URLs, comptes par defaut a changer).
2. Maintenir `docs/architecture_technique.md` (architecture + conventions).
3. Mettre a jour `TODO.md` a chaque progression reelle.
4. Produire un point de statut avant livraison (fait / en cours / blocages).

## Definition de termine (DoD)

- Les routes front et admin principales sont en place et nommees.
- Les CRUD critiques du backoffice sont testables localement.
- Les conventions SEO de base sont appliquees sur les pages publiques.
- Le projet demarre avec Docker sans etapes implicites.
- Les tests essentiels passent localement.
- README, architecture technique et TODO sont aligns avec l'etat reel.
