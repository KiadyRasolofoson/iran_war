# Instructions Copilot - Projet Laravel Iran War

## Conventions generales

- Repondre et documenter en francais.
- Prioriser la lisibilite et la simplicite (controllers explicites, vues Blade claires).
- Eviter les abstractions prematurees.

## Nommage et slugs

- Utiliser `kebab-case` pour les slugs publics.
- Les slugs doivent etre uniques, stables et derives du titre.
- Ne jamais exposer un identifiant interne dans l'URL publique si un slug existe.

## SEO

- Chaque page publique doit definir un `title` unique.
- Chaque page publique doit definir une `meta description` pertinente.
- Ajouter une balise canonical pour les pages indexables.
- Respecter une structure semantique propre (`h1` unique, hierarchie `h2`/`h3`).

## Conventions Laravel (Blade / Controller)

- Frontoffice: routes nommees `front.*`, vues dans `resources/views/front/`.
- Backoffice: routes nommees `admin.*`, prefixe URL `/admin`, vues dans `resources/views/admin/`.
- Controllers front dans `App\\Http\\Controllers\\Front`.
- Controllers admin dans `App\\Http\\Controllers\\Admin`.
- Validation via `FormRequest` pour les formulaires non triviaux.

## Qualite et tests

- Ajouter ou ajuster des tests feature lors de toute nouvelle route metier.
- Verifier les reponses HTTP, la protection des routes admin et le rendu des metadonnees SEO critiques.
