require("dotenv").config();

const app = require("./app");
const prisma = require("./lib/prisma");

const port = Number(process.env.PORT || 4000);

const server = app.listen(port, () => {
  console.log(JSON.stringify({ level: "info", message: "server_started", port }));
});

const shutdown = async (signal) => {
  console.log(JSON.stringify({ level: "info", message: "shutdown_signal", signal }));
  server.close(async () => {
    await prisma.$disconnect();
    process.exit(0);
  });
};

process.on("SIGINT", () => shutdown("SIGINT"));
process.on("SIGTERM", () => shutdown("SIGTERM"));
