function slugify(value) {
  return String(value || "")
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, "")
    .replace(/\s+/g, "-")
    .replace(/-+/g, "-")
    .replace(/^-+|-+$/g, "");
}

async function uniqueSlug(modelName, baseValue, whereExtra = {}) {
  const prisma = require("./prisma");
  const baseSlug = slugify(baseValue) || `item-${Date.now()}`;
  let candidate = baseSlug;
  let counter = 1;

  while (true) {
    const existing = await prisma[modelName].findFirst({
      where: {
        slug: candidate,
        ...whereExtra
      },
      select: { id: true }
    });

    if (!existing) {
      return candidate;
    }

    counter += 1;
    candidate = `${baseSlug}-${counter}`;
  }
}

module.exports = {
  slugify,
  uniqueSlug
};
