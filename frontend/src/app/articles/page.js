import ArticleCard from "@/components/ArticleCard";
import { getArticles } from "@/lib/api";

export const metadata = {
  title: "Articles",
  description: "Liste des articles disponibles."
};

export default async function ArticlesPage() {
  const articles = await getArticles();

  return (
    <section className="grid" style={{ gap: "1rem" }}>
      <h1>Articles</h1>
      <p className="muted">Toutes les publications disponibles sur la plateforme.</p>
      <div className="grid grid-3">
        {articles.map((article) => (
          <ArticleCard key={article.slug} article={article} />
        ))}
      </div>
    </section>
  );
}