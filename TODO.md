# TODO - Projet Guerre en Iran (Next.js + Node.js)

Etat observe du depot au 30/03/2026:
- Fichiers presents: `README.md`, `.docs/CDC_MiniProjet_WebDesign_2026.pdf`
- Aucun code applicatif FrontOffice/BackOffice detecte

## Phase 0 - Cadrage et setup repo

### A faire
- [ ] Aligner le `README.md` avec la stack cible Next.js + Node.js
- [ ] Definir l'architecture finale FO/BO et la convention de dossiers
- [ ] Initialiser les applications (FrontOffice et BackOffice)

### En cours
- [ ] Aucun element en cours

### Termine
- [x] Depot Git initialise (`.git` present)
- [x] CDC disponible (`.docs/CDC_MiniProjet_WebDesign_2026.pdf`)
- [x] Fichier `README.md` present

## Phase 1 - FrontOffice (FO)

### A faire
- [ ] FO-01 Page d'accueil (derniers articles + article principal)
- [ ] FO-02 Liste paginee des articles (filtres categorie/date)
- [ ] FO-03 Page detail article (titre, contenu, image, date, auteur, categorie)
- [ ] FO-04 Navigation par categorie
- [ ] FO-05 Moteur de recherche full-text
- [ ] FO-06 Page A propos

### En cours
- [ ] Aucun element en cours

### Termine
- [ ] Aucun element termine

## Phase 2 - BackOffice (BO)

### A faire
- [ ] BO-01 Authentification securisee sur `/login/`
- [ ] BO-02 Dashboard (stats, derniers contenus, acces rapides)
- [ ] BO-03 CRUD complet Articles
- [ ] BO-04 CRUD Categories
- [ ] BO-05 Gestion des utilisateurs admin
- [ ] BO-06 Gestion des medias (upload images)
- [ ] BO-07 Workflow Brouillon <-> Publie

### En cours
- [ ] Aucun element en cours

### Termine
- [ ] Aucun element termine

## Phase 3 - Base de donnees et contenu

### A faire
- [ ] Creer le schema `users`, `categories`, `articles`
- [ ] Ajouter les champs SEO articles (`slug`, `meta_title`, `meta_description`, `image_alt`)
- [ ] Mettre en place migrations et seeders de base
- [ ] Prevoir contraintes d'integrite (unicite, cles etrangeres, statuts)

### En cours
- [ ] Aucun element en cours

### Termine
- [ ] Aucun element termine

## Phase 4 - SEO et qualite

### A faire
- [ ] URL normalisees et lisibles (`/article/:slug`, `/categorie/:slug`)
- [ ] Hierarchie HTML validee (un seul `h1`, puis `h2/h3` coherents)
- [ ] Balises meta par page (`title`, `description`, `charset`, `viewport`)
- [ ] Open Graph minimal (`og:title`, `og:description`, `og:image`)
- [ ] Attribut `alt` sur toutes les images (dont `alt=''` decoratif)
- [ ] Test Lighthouse mobile (Perf > 70, Accessibilite > 80, SEO > 90, Bonnes pratiques > 80)
- [ ] Test Lighthouse desktop (Perf > 70, Accessibilite > 80, SEO > 90, Bonnes pratiques > 80)

### En cours
- [ ] Aucun element en cours

### Termine
- [ ] Aucun element termine

## Phase 5 - Docker et exploitation

### A faire
- [ ] Ajouter `docker-compose.yml` fonctionnel (app + base de donnees)
- [ ] Configurer les variables via `.env`
- [ ] Monter des volumes persistants (BDD, uploads)
- [ ] Verifier demarrage avec `docker-compose up --build`

### En cours
- [ ] Aucun element en cours

### Termine
- [ ] Aucun element termine

## Phase 6 - Livrables finaux

### A faire
- [ ] Archive ZIP fonctionnelle sans `node_modules`
- [ ] Depot public GitHub/GitLab avec commits significatifs
- [ ] Document technique (captures FO/BO, schema BDD, credentials, ETU, Lighthouse)
- [ ] Verification finale de toutes les exigences obligatoires du CDC

### En cours
- [ ] Aucun element en cours

### Termine
- [ ] Aucun element termine
