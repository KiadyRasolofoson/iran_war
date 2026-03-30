import { getArticles } from "@/lib/api";

export const metadata = {
  title: "Admin Articles",
  description: "Gestion des articles dans le BackOffice."
};

export default async function AdminArticlesPage() {
  const articles = await getArticles();

  return (
    <section className="grid" style={{ gap: "1rem" }}>
      <h1>Admin - Articles</h1>
      <div className="table-wrap panel">
        <table className="table">
          <thead>
            <tr>
              <th>Titre</th>
              <th>Categorie</th>
              <th>Date</th>
              <th>Etat</th>
            </tr>
          </thead>
          <tbody>
            {articles.map((article) => (
              <tr key={article.slug}>
                <td>{article.title}</td>
                <td>{article.categoryName}</td>
                <td>{article.publishedAt}</td>
                <td>
                  <span className="badge">publie</span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  );
}