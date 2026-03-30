const express = require("express");
const { listMedia, createMedia, deleteMedia } = require("../controllers/mediaController");
const { requireAuth } = require("../middlewares/auth");

const router = express.Router();

router.get("/", requireAuth, listMedia);
router.post("/", requireAuth, createMedia);
router.delete("/:id", requireAuth, deleteMedia);

module.exports = router;
