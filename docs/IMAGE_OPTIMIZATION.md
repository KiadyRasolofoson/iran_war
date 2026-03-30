<!-- Template pour les images avec lazy loading et SEO -->

<!-- IMAGES D'ARTICLE (avec alt text obligatoire) -->
<img 
    src="/uploads/article-image.jpg" 
    alt="Description précise et descriptive de l'image" 
    loading="lazy"
    width="800"
    height="600"
    class="article-card-image"
>

<!-- IMAGES HERO (section principale) -->
<img 
    src="/uploads/hero-image.jpg"
    alt="Titre/contexte de l'image pour le héros"
    loading="lazy"
    width="1200"
    height="600"
    class="main-article img"
>

<!-- OPTIMISATIONS RECOMMANDÉES -->

### Pour les images d'article :
1. Utiliser `loading="lazy"` sur toutes les images non critiques
2. Toujours inclure `alt` texte descriptif (5-10 mots)
3. Ajouter `width` et `height` pour éviter le layout shift
4. Compresser images avant upload (max 500KB)
5. Utiliser jpg pour photos, png pour graphiques

### Format recommandé pour alt text :
- Décrire le contenu pertinent
- Ne pas commencer par "image de" ou "photo de"
- Exemple MAUVAIS: "image de la guerre"
- Exemple BON: "Chars militaires iraniens alignés pour le déploiement"

### Chemins des images :
- Uploads utilisateur : `/uploads/` (géré par Uploader)
- Assets statiques : `/assets/images/`
- Icons : `/assets/icons/`

### Script pour ajouter lazy loading global (optionnel) :
```html
<script>
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img:not([loading])');
    images.forEach(img => {
        if (!img.classList.contains('hero') && !img.classList.contains('logo')) {
            img.loading = 'lazy';
        }
    });
});
</script>
```
