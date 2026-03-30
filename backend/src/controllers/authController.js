const jwt = require("jsonwebtoken");
const bcrypt = require("bcryptjs");
const prisma = require("../lib/prisma");

function signToken(user) {
  return jwt.sign(
    {
      sub: user.id,
      role: user.role,
      username: user.username
    },
    process.env.JWT_SECRET,
    { expiresIn: process.env.JWT_EXPIRES_IN || "7d" }
  );
}

async function login(req, res, next) {
  try {
    const { username, password } = req.body;
    if (!username || !password) {
      return res.status(400).json({ error: "username and password are required." });
    }

    const user = await prisma.user.findUnique({ where: { username } });
    if (!user) {
      return res.status(401).json({ error: "Invalid credentials." });
    }

    const isValid = await bcrypt.compare(password, user.passwordHash);
    if (!isValid) {
      return res.status(401).json({ error: "Invalid credentials." });
    }

    const token = signToken(user);
    return res.json({
      token,
      user: {
        id: user.id,
        username: user.username,
        email: user.email,
        role: user.role
      }
    });
  } catch (error) {
    return next(error);
  }
}

async function register(req, res, next) {
  try {
    const { username, email, password } = req.body;
    if (!username || !email || !password) {
      return res.status(400).json({ error: "username, email and password are required." });
    }

    if (password.length < 6) {
      return res.status(400).json({ error: "Password must be at least 6 characters." });
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
      data: {
        username,
        email,
        passwordHash,
        role: "EDITOR"
      },
      select: { id: true, username: true, email: true, role: true }
    });

    const token = signToken(user);
    return res.status(201).json({ token, user });
  } catch (error) {
    return next(error);
  }
}

async function me(req, res) {
  return res.json({ user: req.user });
}

module.exports = {
  login,
  register,
  me
};
