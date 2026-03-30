import Link from "next/link";

export const metadata = {
  title: "Admin",
  description: "Tableau de bord BackOffice."
};

const adminLinks = [
  {
    href: "/admin/articles",
    title: "Gestion des articles",
    description: "Lister, preparer et organiser les publications."
  },
  {
    href: "/admin/categories",
    title: "Gestion des categories",
    description: "Maintenir l'arborescence editoriale."
  },
  {
    href: "/admin/users",
    title: "Gestion des utilisateurs",
    description: "Consulter les comptes et les roles."
  }
];

export default function AdminHomePage() {
  return (
    <section className="grid" style={{ gap: "1rem" }}>
      <div className="panel">
        <h1>Administration</h1>
        <p className="muted">Acces rapide aux modules BackOffice.</p>
      </div>

      <div className="grid grid-3">
        {adminLinks.map((item) => (
          <article key={item.href} className="admin-card panel">
            <h2>{item.title}</h2>
            <p className="muted">{item.description}</p>
            <Link href={item.href} className="btn">
              Ouvrir
            </Link>
          </article>
        ))}
      </div>
    </section>
  );
}