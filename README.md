# Ecommerce Store

A reusable PHP e-commerce foundation for small shops and service businesses. It demonstrates a complete customer-facing purchase flow without requiring a frontend framework or CMS.

## Included

- PHP 8.2+
- Composer
- Configuration-driven product catalogue
- Local SVG product image assets
- Responsive storefront and product pages
- Session-based basket
- Server-side product pricing and totals
- SQLite order persistence via PDO
- Stripe Checkout using the Stripe PHP SDK and a server-side secret key
- Return-page payment verification against the Stripe Checkout Session
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

The first request creates `database/store.sqlite` and seeds the configured catalogue. The database file is ignored by Git.

## Configuration

Store content, currency, checkout countries, shipping copy and demo products live in `config/store.php`. Product images are local assets under `public/images/products/`. Secrets and environment-specific settings live in `.env`.

Required payment variable:

```text
STRIPE_SECRET_KEY=sk_test_...
```

Required email variables follow the same SMTP convention used by the other reusable projects in this collection. See `docs/EMAIL.md` and `docs/STRIPE.md`.

Never commit `.env`, Stripe secret keys or SMTP credentials.

## Checkout architecture

The browser submits only product identifiers and quantities. The server resolves products and prices from the configured catalogue, creates a pending order, then creates a Stripe Checkout Session using server-side amounts.

After a successful card payment, Stripe returns the customer to `/checkout/success`. The server retrieves the Checkout Session using the secret key and verifies its paid status, local order reference, amount and currency before marking the order as paid. The confirmation email is sent after that verification.

There is intentionally no webhook endpoint or webhook signing secret. Because confirmation happens on the success return, a payment is not automatically processed by this application when a customer completes payment but never returns to the site.

The demonstration catalogue uses free UK delivery. A production deployment must define its own tax, shipping, inventory, refund, cancellation, retention and legal requirements.

## Structure

```text
config/          Store and product configuration
database/        Local SQLite database files (ignored)
docs/            Customisation, deployment, email, Stripe and roadmap guides
public/          Web root, routing, CSS and product image assets
src/             Bootstrap, cart/order, contact and Stripe logic
templates/       Page templates
tests/           Smoke tests
```

## Routes

`/` · `/products` · `/products/{slug}` · `/cart` · `/checkout` · `/checkout/success` · `/checkout/cancel` · `/contact` · `/privacy` · `/terms`

## Production notes

Set the web server document root to `public/`, install dependencies with `composer install --no-dev --optimize-autoloader`, configure a real `.env`, enable HTTPS, use a production Stripe secret, and test a complete card checkout including cancellation and return-page verification before accepting real orders.

## Licence and support

Released under the MIT License. See `LICENSE`.

If this project is useful and you choose to use it, voluntary support or a donation is appreciated, but never required.
