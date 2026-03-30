require("dotenv").config();

const bcrypt = require("bcryptjs");
const { PrismaClient } = require("@prisma/client");

const prisma = new PrismaClient();

async function main() {
  const passwordHash = await bcrypt.hash("admin123", 10);

  await prisma.user.upsert({
    where: { username: "admin" },
    update: {
      email: "admin@example.com",
      passwordHash,
      role: "ADMIN"
    },
    create: {
      username: "admin",
      email: "admin@example.com",
      passwordHash,
      role: "ADMIN"
    }
  });

  console.log("Seed complete: admin/admin123");
}

main()
  .catch((error) => {
    console.error(error);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
