# TODO - Projet Iran War

Etat reel des taches, base sur l'implementation actuelle du depot.

## FrontOffice

- [x] Pages publiques en place: accueil, liste articles, detail article, categorie, a-propos.
- [x] Navigation front fonctionnelle.
- [x] Recherche et filtres (categorie, date) sur la liste d'articles.
- [ ] Fonctions front avancees (contact, timeline, contenus enrichis).

## BackOffice

- [x] Authentification admin (login/logout) avec session.
- [x] Dashboard admin accessible apres connexion.
- [x] CRUD articles (create, read, update, delete) + publication/depublication.
- [x] CRUD categories.
- [x] CRUD utilisateurs (routes + vues + controleurs).
- [x] Protection CSRF sur formulaires sensibles.
- [x] Upload image article.
- [ ] Journalisation des actions sensibles (audit log).
- [ ] Gestion de permissions fine (au-dela du role admin/editor actuel).

## BDD

- [x] Schema SQL present dans `db/init.sql` (users, categories, articles).
- [x] Relations SQL et index principaux en place.
- [x] Seed initial present (admin + categories).
- [ ] Migrations versionnees (absentes, script SQL unique actuellement).
- [ ] Procedure de sauvegarde/restauration documentee et testee.

## SEO

- [x] Meta title et meta description dynamiques sur front.
- [x] Balises Open Graph de base dans le layout front.
- [x] URL propres basees sur slug (`/article/{slug}`, `/categorie/{slug}`).
- [x] URL canonique calculee dans le layout front.
- [ ] SEO avance: `sitemap.xml`, `robots.txt`, balisage Schema.org.

## Docker

- [x] `Dockerfile` PHP 8.2 Apache present.
- [x] `docker-compose.yml` present (web + mysql + volumes persistants).
- [x] Variables d'environnement documentees via `.env.example`.
- [ ] Healthcheck compose explicite.
- [ ] Workflow dev/prod separe (profilage ou compose override).

## Documentation et livrables

- [x] README de demarrage Docker mis a jour.
- [x] Documentation technique (`docs/TECHNICAL.md`) creee.
- [ ] Captures Lighthouse a produire et integrer dans `docs/lighthouse/`.
- [ ] Documentation utilisateur finale.
