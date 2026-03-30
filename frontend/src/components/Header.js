import Link from "next/link";

const navLinks = [
  { href: "/", label: "Accueil" },
  { href: "/articles", label: "Articles" },
  { href: "/about", label: "A propos" },
  { href: "/login", label: "Connexion" },
  { href: "/admin", label: "Admin" }
];

export default function Header() {
  return (
    <header className="topbar">
      <div className="container topbar-inner">
        <Link href="/" className="brand">
          Iran War
        </Link>
        <nav className="nav" aria-label="Navigation principale">
          {navLinks.map((link) => (
            <Link key={link.href} href={link.href} className="nav-link">
              {link.label}
            </Link>
          ))}
        </nav>
      </div>
    </header>
  );
}