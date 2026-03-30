export const metadata = {
  title: "A propos",
  description: "Presentation du projet Iran War."
};

export default function AboutPage() {
  return (
    <section className="panel">
      <h1>A propos</h1>
      <p>
        Cette application Next.js est preparee pour un FrontOffice public et un BackOffice administratif.
      </p>
      <p className="muted">
        Elle inclut une couche API client avec fallback mock pour rester fonctionnelle meme si le backend
        est indisponible.
      </p>
    </section>
  );
}