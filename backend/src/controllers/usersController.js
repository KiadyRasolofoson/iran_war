const bcrypt = require("bcryptjs");
const prisma = require("../lib/prisma");

const allowedRoles = new Set(["ADMIN", "EDITOR"]);

async function listUsers(_req, res, next) {
  try {
    const users = await prisma.user.findMany({
      orderBy: { createdAt: "desc" },
      select: {
        id: true,
        username: true,
        email: true,
        role: true,
        createdAt: true,
        updatedAt: true
      }
    });

    return res.json({ items: users });
  } catch (error) {
    return next(error);
  }
}

async function createUser(req, res, next) {
  try {
    const { username, email, password, role = "EDITOR" } = req.body;

    if (!username || !email || !password) {
      return res.status(400).json({ error: "username, email and password are required." });
    }

    const normalizedRole = String(role).toUpperCase();
    if (!allowedRoles.has(normalizedRole)) {
      return res.status(400).json({ error: "Invalid role." });
    }

    const existing = await prisma.user.findFirst({
      where: {
        OR: [{ username }, { email }]
      },
      select: { id: true }
    });

    if (existing) {
      return res.status(409).json({ error: "Username or email already exists." });
    }

    const passwordHash = await bcrypt.hash(password, 10);
    const user = await prisma.user.create({
      data: { username, email, passwordHash, role: normalizedRole },
      select: { id: true, username: true, email: true, role: true, createdAt: true }
    });

    return res.status(201).json(user);
  } catch (error) {
    return next(error);
  }
}

async function updateUser(req, res, next) {
  try {
    const userId = Number(req.params.id);
    if (!Number.isInteger(userId)) {
      return res.status(400).json({ error: "Invalid user id." });
    }

    const { username, email, password, role } = req.body;
    const data = {};

    if (username) {
      data.username = username;
    }
    if (email) {
      data.email = email;
    }
    if (password) {
      data.passwordHash = await bcrypt.hash(password, 10);
    }
    if (role) {
      const normalizedRole = String(role).toUpperCase();
      if (!allowedRoles.has(normalizedRole)) {
        return res.status(400).json({ error: "Invalid role." });
      }
      data.role = normalizedRole;
    }

    const user = await prisma.user.update({
      where: { id: userId },
      data,
      select: {
        id: true,
        username: true,
        email: true,
        role: true,
        updatedAt: true
      }
    });

    return res.json(user);
  } catch (error) {
    if (error.code === "P2025") {
      return res.status(404).json({ error: "User not found." });
    }
    if (error.code === "P2002") {
      return res.status(409).json({ error: "Username or email already exists." });
    }
    return next(error);
  }
}

async function deleteUser(req, res, next) {
  try {
    const userId = Number(req.params.id);
    if (!Number.isInteger(userId)) {
      return res.status(400).json({ error: "Invalid user id." });
    }

    if (req.user.id === userId) {
      return res.status(400).json({ error: "You cannot delete your own account." });
    }

    await prisma.user.delete({ where: { id: userId } });
    return res.status(204).send();
  } catch (error) {
    if (error.code === "P2025") {
      return res.status(404).json({ error: "User not found." });
    }
    return next(error);
  }
}

module.exports = {
  listUsers,
  createUser,
  updateUser,
  deleteUser
};
