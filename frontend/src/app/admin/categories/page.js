import { getCategories } from "@/lib/api";

export const metadata = {
  title: "Admin Categories",
  description: "Gestion des categories dans le BackOffice."
};

export default async function AdminCategoriesPage() {
  const categories = await getCategories();

  return (
    <section className="grid" style={{ gap: "1rem" }}>
      <h1>Admin - Categories</h1>
      <div className="grid grid-2">
        {categories.map((category) => (
          <article key={category.slug} className="panel">
            <h2>{category.name}</h2>
            <p className="muted">{category.description}</p>
          </article>
        ))}
      </div>
    </section>
  );
}