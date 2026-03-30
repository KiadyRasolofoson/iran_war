const prisma = require("../lib/prisma");
const { uniqueSlug } = require("../lib/slug");

const allowedStatuses = new Set(["DRAFT", "PUBLISHED"]);

function canManageArticle(user, article) {
  if (!user) {
    return false;
  }
  return user.role === "ADMIN" || user.id === article.authorId;
}

async function listArticles(req, res, next) {
  try {
    const includeDraft = req.user && req.query.includeDraft === "true";
    const statusFilter = req.query.status ? String(req.query.status).toUpperCase() : null;

    const where = {};
    if (!includeDraft) {
      where.status = "PUBLISHED";
    }
    if (statusFilter && allowedStatuses.has(statusFilter)) {
      where.status = statusFilter;
    }

    const items = await prisma.article.findMany({
      where,
      orderBy: [{ publishedAt: "desc" }, { createdAt: "desc" }],
      include: {
        category: { select: { id: true, name: true, slug: true } },
        author: { select: { id: true, username: true } }
      }
    });

    return res.json({ items });
  } catch (error) {
    return next(error);
  }
}

async function getArticleBySlug(req, res, next) {
  try {
    const { slug } = req.params;
    const article = await prisma.article.findUnique({
      where: { slug },
      include: {
        category: { select: { id: true, name: true, slug: true } },
        author: { select: { id: true, username: true } }
      }
    });

    if (!article) {
      return res.status(404).json({ error: "Article not found." });
    }

    if (article.status === "DRAFT") {
      const canAccessDraft = req.user && canManageArticle(req.user, article);
      if (!canAccessDraft) {
        return res.status(404).json({ error: "Article not found." });
      }
    }

    return res.json(article);
  } catch (error) {
    return next(error);
  }
}

async function createArticle(req, res, next) {
  try {
    const { title, content, excerpt, categoryId, status = "DRAFT" } = req.body;

    if (!title || !content || !categoryId) {
      return res.status(400).json({ error: "title, content and categoryId are required." });
    }

    const normalizedStatus = String(status).toUpperCase();
    if (!allowedStatuses.has(normalizedStatus)) {
      return res.status(400).json({ error: "Invalid status." });
    }

    const category = await prisma.category.findUnique({ where: { id: Number(categoryId) } });
    if (!category) {
      return res.status(400).json({ error: "Invalid categoryId." });
    }

    const slug = await uniqueSlug("article", title);
    const article = await prisma.article.create({
      data: {
        title,
        slug,
        content,
        excerpt: excerpt || null,
        status: normalizedStatus,
        publishedAt: normalizedStatus === "PUBLISHED" ? new Date() : null,
        categoryId: Number(categoryId),
        authorId: req.user.id
      },
      include: {
        category: { select: { id: true, name: true, slug: true } },
        author: { select: { id: true, username: true } }
      }
    });

    return res.status(201).json(article);
  } catch (error) {
    return next(error);
  }
}

async function updateArticle(req, res, next) {
  try {
    const articleId = Number(req.params.id);
    if (!Number.isInteger(articleId)) {
      return res.status(400).json({ error: "Invalid article id." });
    }

    const article = await prisma.article.findUnique({ where: { id: articleId } });
    if (!article) {
      return res.status(404).json({ error: "Article not found." });
    }

    if (!canManageArticle(req.user, article)) {
      return res.status(403).json({ error: "Insufficient permissions." });
    }

    const { title, content, excerpt, categoryId, status } = req.body;
    const data = {};

    if (typeof title === "string" && title.trim()) {
      data.title = title;
      data.slug = await uniqueSlug("article", title, { NOT: { id: articleId } });
    }

    if (content !== undefined) {
      data.content = content;
    }

    if (excerpt !== undefined) {
      data.excerpt = excerpt || null;
    }

    if (categoryId !== undefined) {
      const category = await prisma.category.findUnique({ where: { id: Number(categoryId) } });
      if (!category) {
        return res.status(400).json({ error: "Invalid categoryId." });
      }
      data.categoryId = Number(categoryId);
    }

    if (status !== undefined) {
      const normalizedStatus = String(status).toUpperCase();
      if (!allowedStatuses.has(normalizedStatus)) {
        return res.status(400).json({ error: "Invalid status." });
      }
      data.status = normalizedStatus;
      if (normalizedStatus === "PUBLISHED" && !article.publishedAt) {
        data.publishedAt = new Date();
      }
      if (normalizedStatus === "DRAFT") {
        data.publishedAt = null;
      }
    }

    const updated = await prisma.article.update({
      where: { id: articleId },
      data,
      include: {
        category: { select: { id: true, name: true, slug: true } },
        author: { select: { id: true, username: true } }
      }
    });

    return res.json(updated);
  } catch (error) {
    return next(error);
  }
}

async function deleteArticle(req, res, next) {
  try {
    const articleId = Number(req.params.id);
    if (!Number.isInteger(articleId)) {
      return res.status(400).json({ error: "Invalid article id." });
    }

    const article = await prisma.article.findUnique({ where: { id: articleId } });
    if (!article) {
      return res.status(404).json({ error: "Article not found." });
    }

    if (!canManageArticle(req.user, article)) {
      return res.status(403).json({ error: "Insufficient permissions." });
    }

    await prisma.article.delete({ where: { id: articleId } });
    return res.status(204).send();
  } catch (error) {
    return next(error);
  }
}

module.exports = {
  listArticles,
  getArticleBySlug,
  createArticle,
  updateArticle,
  deleteArticle
};
