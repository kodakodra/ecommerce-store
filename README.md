# Ecommerce Store

A reusable PHP e-commerce foundation for small shops and service businesses. It demonstrates a complete customer-facing purchase flow without requiring a frontend framework or CMS.

## Included

- PHP 8.2+
- Composer
- Configuration-driven product catalogue
- Responsive storefront and product pages
- Session-based basket
- Server-side product pricing and totals
- SQLite order persistence via PDO
- Stripe Checkout using the Stripe PHP SDK
- Signed Stripe webhook processing
- PHPMailer SMTP contact and order-confirmation email
- CSRF protection, honeypot anti-spam and contact throttling
- Security response headers and production-safe error handling
- Privacy and terms starter pages
- `robots.txt` and `sitemap.xml`
- Smoke tests and GitHub Actions for PHP 8.2 and 8.3
- Apache and PHP built-in server routing
- Documentation and MIT licensing

## Quick start

Requirements: PHP 8.2+, Composer and the PHP SQLite extension.

```bash
composer install
cp .env.example .env
composer serve
```

Open `http://localhost:8000`.

Run the checks with:

```bash
composer test
```

The first request creates `database/store.sqlite` and seeds the configured demo products. The database file is ignored by Git.

## Configuration

Store content, currency, checkout countries and demo products live in `config/store.php`. Secrets and environment-specific settings live in `.env`.

Required payment variables:

```text
STRIPE_SECRET_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

Required email variables follow the same SMTP convention used by the other reusable projects in this collection. See `docs/EMAIL.md` and `docs/STRIPE.md`.

Never commit `.env`, Stripe secret keys or SMTP credentials.

## Checkout architecture

The browser submits only product identifiers and quantities. The server resolves products and prices from the configured catalogue, creates a pending order, then creates a Stripe Checkout Session using server-side amounts.

Payment confirmation is handled by the signed Stripe webhook. The success page does not mark orders as paid. Webhook processing is idempotent using stored Stripe event IDs, and paid orders can trigger a confirmation email.

This is a reusable foundation, not a production-ready fulfilment system. A real deployment must define tax, shipping, inventory, refund, cancellation, retention and legal requirements for the business.

## Structure

```text
config/          Store and product configuration
database/        Local SQLite database files (ignored)
docs/            Customisation, deployment, email, Stripe and roadmap guides
public/          Web root and routing
src/             Bootstrap, cart/order, contact, Stripe and webhook logic
templates/       Page templates
tests/           Smoke tests
```

## Routes

`/` · `/products` · `/products/{slug}` · `/cart` · `/checkout` · `/checkout/success` · `/checkout/cancel` · `/contact` · `/privacy` · `/terms` · `/webhooks/stripe`

## Production notes

Set the web server document root to `public/`, install dependencies with `composer install --no-dev --optimize-autoloader`, configure a real `.env`, enable HTTPS, configure Stripe's production webhook endpoint, and test a complete checkout plus webhook delivery before accepting real orders.

## Licence and support

Released under the MIT License. See `LICENSE`.

If this project is useful and you choose to use it, voluntary support or a donation is appreciated, but never required.
