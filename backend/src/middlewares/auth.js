const jwt = require("jsonwebtoken");
const prisma = require("../lib/prisma");

function getTokenFromHeader(authHeader) {
  if (!authHeader || !authHeader.startsWith("Bearer ")) {
    return null;
  }

  return authHeader.slice(7);
}

async function requireAuth(req, res, next) {
  try {
    const token = getTokenFromHeader(req.headers.authorization);
    if (!token) {
      return res.status(401).json({ error: "Authentication token is missing." });
    }

    const payload = jwt.verify(token, process.env.JWT_SECRET);
    const user = await prisma.user.findUnique({
      where: { id: payload.sub },
      select: { id: true, username: true, email: true, role: true }
    });

    if (!user) {
      return res.status(401).json({ error: "User not found for this token." });
    }

    req.user = user;
    return next();
  } catch (error) {
    return res.status(401).json({ error: "Invalid or expired token." });
  }
}

function optionalAuth(req, _res, next) {
  try {
    const token = getTokenFromHeader(req.headers.authorization);
    if (!token) {
      return next();
    }

    const payload = jwt.verify(token, process.env.JWT_SECRET);
    req.user = {
      id: payload.sub,
      role: payload.role,
      username: payload.username
    };
    return next();
  } catch (_error) {
    return next();
  }
}

function requireRole(...roles) {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ error: "Authentication required." });
    }

    if (!roles.includes(req.user.role)) {
      return res.status(403).json({ error: "Insufficient permissions." });
    }

    return next();
  };
}

module.exports = {
  requireAuth,
  optionalAuth,
  requireRole
};
