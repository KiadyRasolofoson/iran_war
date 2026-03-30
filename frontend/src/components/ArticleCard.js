import Link from "next/link";

function getArticleImageAlt(article) {
  if (article.imageAlt && article.imageAlt.trim()) {
    return article.imageAlt;
  }

  return `Illustration de l'article ${article.title}`;
}

export default function ArticleCard({ article }) {
  return (
    <article className="article-card">
      <img
        className="article-image"
        src={article.imageUrl}
        alt={getArticleImageAlt(article)}
        loading="lazy"
      />
      <div className="article-body">
        <p className="article-meta">
          <span className="badge">{article.categoryName}</span> - {article.publishedAt}
        </p>
        <h3>{article.title}</h3>
        <p className="muted">{article.excerpt}</p>
        <Link href={`/articles/${article.slug}`} className="btn">
          Lire l'article
        </Link>
      </div>
    </article>
  );
}