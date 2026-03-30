const prisma = require("../lib/prisma");

function isValidHttpUrl(value) {
  try {
    const parsed = new URL(value);
    return parsed.protocol === "http:" || parsed.protocol === "https:";
  } catch (_error) {
    return false;
  }
}

async function listMedia(_req, res, next) {
  try {
    const items = await prisma.media.findMany({
      orderBy: { createdAt: "desc" },
      include: {
        uploader: { select: { id: true, username: true } },
        article: { select: { id: true, title: true, slug: true } }
      }
    });

    return res.json({ items });
  } catch (error) {
    return next(error);
  }
}

async function createMedia(req, res, next) {
  try {
    const { url, alt, articleId } = req.body;

    if (!url || !isValidHttpUrl(url)) {
      return res.status(400).json({ error: "A valid http/https url is required." });
    }

    let parsedArticleId = null;
    if (articleId !== undefined && articleId !== null) {
      parsedArticleId = Number(articleId);
      if (!Number.isInteger(parsedArticleId)) {
        return res.status(400).json({ error: "Invalid articleId." });
      }

      const article = await prisma.article.findUnique({ where: { id: parsedArticleId } });
      if (!article) {
        return res.status(400).json({ error: "Invalid articleId." });
      }
    }

    const media = await prisma.media.create({
      data: {
        url,
        alt: alt || null,
        articleId: parsedArticleId,
        uploaderId: req.user.id
      },
      include: {
        uploader: { select: { id: true, username: true } },
        article: { select: { id: true, title: true, slug: true } }
      }
    });

    return res.status(201).json(media);
  } catch (error) {
    return next(error);
  }
}

async function deleteMedia(req, res, next) {
  try {
    const mediaId = Number(req.params.id);
    if (!Number.isInteger(mediaId)) {
      return res.status(400).json({ error: "Invalid media id." });
    }

    const media = await prisma.media.findUnique({ where: { id: mediaId } });
    if (!media) {
      return res.status(404).json({ error: "Media not found." });
    }

    const canDelete = req.user.role === "ADMIN" || req.user.id === media.uploaderId;
    if (!canDelete) {
      return res.status(403).json({ error: "Insufficient permissions." });
    }

    await prisma.media.delete({ where: { id: mediaId } });
    return res.status(204).send();
  } catch (error) {
    return next(error);
  }
}

module.exports = {
  listMedia,
  createMedia,
  deleteMedia
};
