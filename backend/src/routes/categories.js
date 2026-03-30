const express = require("express");
const {
  listCategories,
  getCategoryBySlug,
  createCategory,
  updateCategory,
  deleteCategory
} = require("../controllers/categoriesController");
const { requireAuth, requireRole } = require("../middlewares/auth");

const router = express.Router();

router.get("/", listCategories);
router.get("/:slug", getCategoryBySlug);
router.post("/", requireAuth, requireRole("ADMIN"), createCategory);
router.patch("/:id", requireAuth, requireRole("ADMIN"), updateCategory);
router.delete("/:id", requireAuth, requireRole("ADMIN"), deleteCategory);

module.exports = router;
