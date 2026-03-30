import { getUsers } from "@/lib/api";

export const metadata = {
  title: "Admin Users",
  description: "Gestion des utilisateurs dans le BackOffice."
};

export default async function AdminUsersPage() {
  const users = await getUsers();

  return (
    <section className="grid" style={{ gap: "1rem" }}>
      <h1>Admin - Utilisateurs</h1>
      <div className="table-wrap panel">
        <table className="table">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Email</th>
              <th>Role</th>
            </tr>
          </thead>
          <tbody>
            {users.map((user) => (
              <tr key={user.id}>
                <td>{user.name}</td>
                <td>{user.email}</td>
                <td>
                  <span className={user.role === "admin" ? "badge" : "muted"}>{user.role}</span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  );
}