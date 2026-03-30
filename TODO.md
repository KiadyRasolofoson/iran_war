# TODO - Projet Iran War

Checklist centrale pour suivre le CDC: FrontOffice, BackOffice, BDD, SEO, Docker, livrables.

## A faire
- [ ] [FrontOffice] Definir la charte UI (couleurs, typo, composants) et la navigation principale.
- [ ] [FrontOffice] Implementer les pages publiques: accueil, contexte historique, timeline, contact.
- [ ] [FrontOffice] Ajouter formulaires avec validations client et messages d'erreur clairs.
- [ ] [BackOffice] Definir les roles (admin, editeur) et les permissions associees.
- [ ] [BackOffice] Implementer CRUD complet pour contenus (articles, medias, categories).
- [ ] [BackOffice] Ajouter journalisation des actions sensibles (connexion, suppression, publication).
- [ ] [BDD] Finaliser le schema relationnel (utilisateurs, contenus, medias, tags, logs).
- [ ] [BDD] Ecrire migrations versionnees et script de seed de donnees initiales.
- [ ] [BDD] Ajouter sauvegarde/restauration documentee et testee.
- [ ] [SEO] Definir meta title/description par page et balises Open Graph.
- [ ] [SEO] Generer sitemap.xml et robots.txt coherents avec les routes.
- [ ] [SEO] Mettre en place URLs propres, canonicals, maillage interne et schema.org minimal.
- [ ] [Docker] Produire Dockerfile app (prod) + Dockerfile dev (hot reload si utile).
- [ ] [Docker] Produire docker-compose pour app + web + postgres + volume persistant.
- [ ] [Docker] Ajouter scripts de demarrage (dev/prod) et verification healthcheck.
- [ ] [Livrables] Rediger README complet (setup, variables env, commandes, runbook).
- [ ] [Livrables] Fournir jeu de donnees de demo et comptes de test.
- [ ] [Livrables] Preparer paquet final: code, dump BDD, doc technique, doc utilisateur.

## En cours
- [ ] [Pilotage] Transformer le CDC en backlog priorise (MVP puis versions).
- [ ] [Pilotage] Definir criteres d'acceptation pour chaque fonctionnalite critique.

## Fait
- [x] [Bootstrap] Depot Git initialise.
- [x] [Bootstrap] Espace de documentation present (.docs/).
- [x] [Bootstrap] TODO projet cree avec suivi par statut (A faire / En cours / Fait).
