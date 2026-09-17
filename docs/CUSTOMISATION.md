# Customisation

## Store configuration
Edit `config/store.php` for store name, description, currency, checkout countries, navigation, social links and products. Product prices are integer minor units (for GBP, pence).

## Environment
Copy `.env.example` to `.env`. Keep `.env` out of Git. Configure the SQLite path, SMTP variables and Stripe keys there.

## Products
The first request creates missing demo products in SQLite from the config. Product names, descriptions and prices are resolved server-side. There is intentionally no admin panel or database catalogue editor in this starter.

## Checkout
The customer submits name and email, the server creates a pending order, and the app creates a hosted Stripe Checkout Session. Card entry stays on Stripe. A signed webhook changes the order to paid.

## Contact email
The contact form and order confirmations use PHPMailer over SMTP. See `docs/EMAIL.md`.

## Security
The starter includes CSRF tokens, a honeypot field, short contact throttling, output escaping, security headers and production-safe error handling. Review these controls and add business-specific protections before production use.
