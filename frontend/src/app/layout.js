import "./globals.css";
import Header from "@/components/Header";
import Footer from "@/components/Footer";

export const metadata = {
  title: {
    default: "Iran War - FrontOffice",
    template: "%s | Iran War"
  },
  description: "Plateforme d'articles et d'administration pour Iran War."
};

export default function RootLayout({ children }) {
  return (
    <html lang="fr">
      <body>
        <div className="site-shell">
          <Header />
          <main className="site-main container">{children}</main>
          <Footer />
        </div>
      </body>
    </html>
  );
}