import ArticleCard from "@/components/ArticleCard";
import { getCategoryBySlug } from "@/lib/api";

export async function generateMetadata({ params }) {
  const resolvedParams = await params;
  const category = await getCategoryBySlug(resolvedParams.slug);

  if (!category) {
    return {
      title: "Categorie introuvable",
      description: "Cette categorie n'existe pas."
    };
  }

  return {
    title: `Categorie: ${category.name}`,
    description: category.description
  };
}

export default async function CategoryPage({ params }) {
  const resolvedParams = await params;
  const category = await getCategoryBySlug(resolvedParams.slug);

  if (!category) {
    return (
      <section className="panel">
        <h1>Categorie introuvable</h1>
        <p className="muted">Aucune categorie correspondante.</p>
      </section>
    );
  }

  return (
    <section className="grid" style={{ gap: "1rem" }}>
      <div className="panel">
        <h1>{category.name}</h1>
        <p className="muted">{category.description}</p>
      </div>

      <div className="grid grid-3">
        {category.articles.map((article) => (
          <ArticleCard key={article.slug} article={article} />
        ))}
      </div>
    </section>
  );
}