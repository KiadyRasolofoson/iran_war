function notFound(req, res, _next) {
  return res.status(404).json({
    error: "Route not found",
    path: req.originalUrl
  });
}

function errorHandler(err, _req, res, _next) {
  const statusCode = Number(err.statusCode || 500);
  const message = err.message || "Internal server error";

  console.error(
    JSON.stringify({
      level: "error",
      message: "request_failed",
      statusCode,
      details: message
    })
  );

  return res.status(statusCode).json({ error: message });
}

module.exports = {
  notFound,
  errorHandler
};
