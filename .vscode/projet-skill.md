# Skill projet - Guerre en Iran (Next.js + Node.js)

## Objectif
Produire un site d'information avec:
- FrontOffice public (consultation)
- BackOffice securise (gestion contenu)
- SEO conforme CDC
- Execution 100% Docker

## Architecture cible (simple et explicite)
- `frontend/`: Next.js (App Router) pour le FrontOffice
- `backend/`: Node.js (API REST) pour BackOffice + logique metier
- `docker-compose.yml`: orchestration app + base de donnees
- `docs/`: captures, schema BDD, rapport technique

## Conventions projet
- Langue UI: francais
- URLs: slugs minuscules avec tirets, sans accents
- Nommage: descriptif et stable (`article`, `categorie`, `utilisateur`)
- Commits: petits, frequents, message explicite
- Ne pas valider de secrets dans Git (`.env` local uniquement)

## Commandes utiles (reference)
- Installer deps FO: `cd frontend && npm install`
- Installer deps BO: `cd backend && npm install`
- Lancer FO dev: `cd frontend && npm run dev`
- Lancer BO dev: `cd backend && npm run dev`
- Lancer Docker: `docker-compose up --build`
- Arreter Docker: `docker-compose down`

## Regles SEO obligatoires
- 1 seul `h1` par page, structure `h2/h3` coherente
- `title` et `meta description` uniques par page
- URLs propres: `/article/:slug`, `/categorie/:slug`
- Toutes les images ont un `alt` pertinent (ou `alt=''` decoratif)
- Viser Lighthouse: Perf > 70, Accessibilite > 80, SEO > 90, Bonnes pratiques > 80

## Routes cles
- FrontOffice:
  - `/`
  - `/articles`
  - `/article/[slug]`
  - `/categorie/[slug]`
  - `/a-propos`
- BackOffice:
  - `/login`
  - `/admin`
  - `/admin/articles`
  - `/admin/categories`
  - `/admin/utilisateurs`

## Regles securite minimales
- Auth obligatoire sur toutes les routes `/admin/*`
- Mots de passe hashes (jamais en clair)
- Validation stricte des entrees (serveur)
- Protection CSRF et gestion de session/token sure
- Controle d'acces par role (`admin`, `editor`)
- Limiter la taille/type des uploads et scanner les extensions
