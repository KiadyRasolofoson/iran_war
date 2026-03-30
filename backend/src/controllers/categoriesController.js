const prisma = require("../lib/prisma");
const { uniqueSlug } = require("../lib/slug");

async function listCategories(_req, res, next) {
  try {
    const categories = await prisma.category.findMany({
      orderBy: { name: "asc" },
      include: {
        _count: {
          select: { articles: true }
        }
      }
    });

    return res.json({ items: categories });
  } catch (error) {
    return next(error);
  }
}

async function getCategoryBySlug(req, res, next) {
  try {
    const { slug } = req.params;
    const category = await prisma.category.findUnique({
      where: { slug },
      include: {
        articles: {
          where: { status: "PUBLISHED" },
          select: {
            id: true,
            title: true,
            slug: true,
            status: true,
            publishedAt: true
          }
        }
      }
    });

    if (!category) {
      return res.status(404).json({ error: "Category not found." });
    }

    return res.json(category);
  } catch (error) {
    return next(error);
  }
}

async function createCategory(req, res, next) {
  try {
    const { name, description } = req.body;
    if (!name) {
      return res.status(400).json({ error: "name is required." });
    }

    const slug = await uniqueSlug("category", name);
    const category = await prisma.category.create({
      data: { name, description: description || null, slug }
    });

    return res.status(201).json(category);
  } catch (error) {
    if (error.code === "P2002") {
      return res.status(409).json({ error: "Category already exists." });
    }
    return next(error);
  }
}

async function updateCategory(req, res, next) {
  try {
    const categoryId = Number(req.params.id);
    if (!Number.isInteger(categoryId)) {
      return res.status(400).json({ error: "Invalid category id." });
    }

    const { name, description } = req.body;
    const data = {};

    if (typeof name === "string" && name.trim()) {
      data.name = name;
      data.slug = await uniqueSlug("category", name, { NOT: { id: categoryId } });
    }

    if (description !== undefined) {
      data.description = description || null;
    }

    const updated = await prisma.category.update({
      where: { id: categoryId },
      data
    });

    return res.json(updated);
  } catch (error) {
    if (error.code === "P2025") {
      return res.status(404).json({ error: "Category not found." });
    }
    return next(error);
  }
}

async function deleteCategory(req, res, next) {
  try {
    const categoryId = Number(req.params.id);
    if (!Number.isInteger(categoryId)) {
      return res.status(400).json({ error: "Invalid category id." });
    }

    await prisma.category.delete({ where: { id: categoryId } });
    return res.status(204).send();
  } catch (error) {
    if (error.code === "P2025") {
      return res.status(404).json({ error: "Category not found." });
    }
    if (error.code === "P2003") {
      return res.status(409).json({ error: "Category is referenced by articles." });
    }
    return next(error);
  }
}

module.exports = {
  listCategories,
  getCategoryBySlug,
  createCategory,
  updateCategory,
  deleteCategory
};
