function getHealth(_req, res) {
  return res.json({
    status: "ok",
    service: "iran-war-cms-backend",
    timestamp: new Date().toISOString()
  });
}

module.exports = {
  getHealth
};
