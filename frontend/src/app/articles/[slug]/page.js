import Link from "next/link";
import { getArticleBySlug } from "@/lib/api";

export async function generateMetadata({ params }) {
  const resolvedParams = await params;
  const article = await getArticleBySlug(resolvedParams.slug);

  if (!article) {
    return {
      title: "Article introuvable",
      description: "Cet article n'existe pas."
    };
  }

  return {
    title: article.title,
    description: article.excerpt
  };
}

export default async function ArticleDetailPage({ params }) {
  const resolvedParams = await params;
  const article = await getArticleBySlug(resolvedParams.slug);

  if (!article) {
    return (
      <section className="panel">
        <h1>Article introuvable</h1>
        <p className="muted">Le contenu demande est indisponible.</p>
        <Link href="/articles" className="btn">
          Retour aux articles
        </Link>
      </section>
    );
  }

  return (
    <article className="panel grid" style={{ gap: "1rem" }}>
      <h1>{article.title}</h1>
      <p className="article-meta">
        <span className="badge">{article.categoryName}</span> - {article.publishedAt}
      </p>
      <img
        className="article-image"
        src={article.imageUrl}
        alt={article.imageAlt || `Illustration de l'article ${article.title}`}
      />
      <p>{article.content}</p>
      <Link href={`/categories/${article.categorySlug}`} className="btn">
        Voir la categorie
      </Link>
    </article>
  );
}