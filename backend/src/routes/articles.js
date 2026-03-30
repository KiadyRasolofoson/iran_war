const express = require("express");
const {
  listArticles,
  getArticleBySlug,
  createArticle,
  updateArticle,
  deleteArticle
} = require("../controllers/articlesController");
const { requireAuth, optionalAuth } = require("../middlewares/auth");

const router = express.Router();

router.get("/", optionalAuth, listArticles);
router.get("/:slug", optionalAuth, getArticleBySlug);
router.post("/", requireAuth, createArticle);
router.patch("/:id", requireAuth, updateArticle);
router.delete("/:id", requireAuth, deleteArticle);

module.exports = router;
