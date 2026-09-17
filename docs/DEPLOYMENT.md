# Deployment

Use a PHP 8.2+ host with the SQLite extension or adapt `src/store.php` to another PDO driver before deployment. Set the web-server document root to `public/` so application source and `.env` are not directly web-accessible.

Install production dependencies with:

```bash
composer install --no-dev --optimize-autoloader
```

Create a production `.env` with a real `SITE_URL`, database path, SMTP credentials and `STRIPE_SECRET_KEY`. Enable HTTPS.

Before accepting real orders, test product pricing, basket behaviour, successful card payment, cancellation, return-page payment verification, email delivery, stock rules, tax, shipping, refunds, legal notices and error handling.
