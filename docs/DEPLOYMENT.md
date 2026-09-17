# Deployment

Use a PHP 8.2+ host with the SQLite extension or adapt `src/store.php` to another PDO driver before deployment. Set the web-server document root to `public/` so application source and `.env` are not directly web-accessible.

Install production dependencies with:

```bash
composer install --no-dev --optimize-autoloader
```

Create a production `.env` with a real `SITE_URL`, database path, SMTP credentials, `STRIPE_SECRET_KEY` and `STRIPE_WEBHOOK_SECRET`. Enable HTTPS.

Configure Stripe's webhook endpoint as:

```text
https://example.com/webhooks/stripe
```

Subscribe to the checkout events required by the application, including `checkout.session.completed` and asynchronous payment success where applicable.

Before accepting real orders, test product pricing, basket behaviour, successful payment, cancellation, webhook delivery, duplicate webhook delivery, email delivery, stock rules, tax, shipping, refunds, legal notices and error handling.
