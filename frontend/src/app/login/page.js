export const metadata = {
  title: "Connexion",
  description: "Connexion a l'espace d'administration."
};

export default function LoginPage() {
  return (
    <section className="login-box" style={{ maxWidth: "520px", margin: "0 auto" }}>
      <h1>Connexion</h1>
      <p className="muted">Accedez au BackOffice avec vos identifiants.</p>

      <form>
        <div className="form-row">
          <label htmlFor="email">Email</label>
          <input id="email" type="email" className="input" placeholder="admin@example.com" />
        </div>
        <div className="form-row">
          <label htmlFor="password">Mot de passe</label>
          <input id="password" type="password" className="input" placeholder="Votre mot de passe" />
        </div>
        <button type="submit" className="btn">
          Se connecter
        </button>
      </form>
    </section>
  );
}