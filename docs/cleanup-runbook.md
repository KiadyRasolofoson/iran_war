# Cleanup Runbook

## Article HTML one-shot cleanup

Run once from the project root:

```bash
docker compose exec -T web php scripts/cleanup_articles_content.php
```

Expected output fields:

- `Scanned rows`
- `Changed rows`
- `URL-normalized count`
- `Nested-columns-flattened count`
- `Changed IDs`
