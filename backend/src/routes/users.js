const express = require("express");
const {
  listUsers,
  createUser,
  updateUser,
  deleteUser
} = require("../controllers/usersController");
const { requireAuth, requireRole } = require("../middlewares/auth");

const router = express.Router();

router.use(requireAuth, requireRole("ADMIN"));
router.get("/", listUsers);
router.post("/", createUser);
router.patch("/:id", updateUser);
router.delete("/:id", deleteUser);

module.exports = router;
