# Agent Skill - Projet Iran War

## 1) Contexte projet
- Projet web pedagogique sur la guerre Iran-Irak.
- Cible: site public (FrontOffice) + administration (BackOffice).
- Stack attendue: PHP natif, PostgreSQL, HTML/CSS/JS, Docker.
- Priorite: code simple, securise, SEO-ready, facile a maintenir.

## 2) Conventions PHP natif
- Version cible: PHP 8.2+.
- Activer `declare(strict_types=1);` dans chaque fichier PHP metier.
- Respecter PSR-12 pour style et nommage.
- Structure en couches simples:
  - `public/` pour point d'entree HTTP.
  - `src/Controller/` pour gestion requete/reponse.
  - `src/Service/` pour logique metier.
  - `src/Repository/` pour acces BDD.
  - `src/Entity/` pour objets de domaine.
- Une responsabilite principale par classe.
- Pas de logique SQL inline dans les vues.
- Toujours utiliser requetes preparees (PDO) et transactions pour operations critiques.

## 3) Securite (obligatoire)
- Authentification:
  - Hash mot de passe avec `password_hash()`.
  - Verification avec `password_verify()`.
  - Sessions securisees (`httponly`, `secure`, regeneration ID apres login).
- Autorisation:
  - Verification explicite du role avant chaque action BackOffice.
- Protection des entrees:
  - Validation serveur sur tous les formulaires.
  - Echappement sortie HTML avec `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- CSRF:
  - Token CSRF sur toutes les requetes POST/PUT/DELETE.
- Upload fichiers:
  - Whitelist MIME/extensions.
  - Renommage serveur.
  - Stockage hors racine publique si possible.
- Erreurs:
  - Jamais afficher stacktrace en production.
  - Logger erreurs techniques avec contexte utile.

## 4) SEO (obligatoire)
- Chaque page publique doit avoir:
  - `title` unique.
  - `meta description` utile.
  - Un seul `h1` coherent avec le sujet.
- URLs lisibles et stables (slug).
- Balise canonical pour eviter contenu duplique.
- Fichiers `sitemap.xml` et `robots.txt` maintenus.
- Images optimisees avec attribut `alt` descriptif.
- Performance:
  - Compresser assets.
  - Activer cache HTTP sur contenus statiques.

## 5) Docker (obligatoire)
- Services minimaux:
  - `app` (PHP-FPM ou Apache selon choix).
  - `web` (Nginx si PHP-FPM).
  - `postgres`.
- Bonnes pratiques:
  - Variables via `.env` et `.env.example`.
  - Volumes nommes pour persistance BDD.
  - Healthcheck pour app et BDD.
  - Commandes standardisees: build, up, down, logs, exec.
- Reproductibilite:
  - Un clone + `docker compose up` doit suffire pour demarrer.

## 6) Structure dossiers recommandee
```text
.
|- .github/
|  |- skills/
|     |- agent-skill.md
|- docker/
|  |- nginx/
|  |- php/
|- public/
|  |- index.php
|  |- assets/
|- src/
|  |- Controller/
|  |- Service/
|  |- Repository/
|  |- Entity/
|  |- Security/
|- config/
|- migrations/
|- tests/
|- docs/
|- docker-compose.yml
|- Dockerfile
|- README.md
```

## 7) Definition of Done (DoD)
Une tache est terminee uniquement si tout est valide:
- Fonctionnel:
  - Feature testee manuellement sur cas nominal + cas erreur.
- Qualite code:
  - Code lisible, sans duplication evidente, conforme conventions PHP.
- Securite:
  - Validation entree, controle acces, CSRF (si formulaire), pas de fuite sensible.
- BDD:
  - Migration creee si schema modifie.
  - Requetes preparees et index verifies pour requetes clefs.
- SEO (FrontOffice):
  - Title, description, h1, URL propre, alt images.
- Docker:
  - Build propre et demarrage compose OK sur environnement vierge.
- Livrables:
  - README mis a jour.
  - Variables d'environnement documentees.
  - Notes de test ajoutees (ce qui a ete verifie).

## 8) Workflow execution agent
- Lire le besoin et lister impacts FrontOffice/BackOffice/BDD/SEO/Docker.
- Implenter par petits lots testables.
- Verifier regressions avant de marquer une tache comme faite.
- Mettre a jour `TODO.md` apres chaque lot.
