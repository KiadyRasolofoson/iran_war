import Link from "next/link";
import ArticleCard from "@/components/ArticleCard";
import { getArticles } from "@/lib/api";

export const metadata = {
  title: "Accueil",
  description: "Page d'accueil du FrontOffice Iran War."
};

export default async function HomePage() {
  const articles = await getArticles();
  const latestArticles = articles.slice(0, 3);

  return (
    <div className="grid" style={{ gap: "1.4rem" }}>
      <section className="hero">
        <h1>Iran War FrontOffice</h1>
        <p className="muted">
          Consultez les analyses, les categories et les publications recentes depuis une interface simple.
        </p>
        <Link href="/articles" className="btn">
          Voir tous les articles
        </Link>
      </section>

      <section>
        <h2>Derniers articles</h2>
        <div className="grid grid-3">
          {latestArticles.map((article) => (
            <ArticleCard key={article.slug} article={article} />
          ))}
        </div>
      </section>
    </div>
  );
}