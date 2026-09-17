# Customisation

## Store configuration
Edit `config/store.php` for store name, description, currency, checkout countries, navigation, social links, shipping copy and products. Product prices are integer minor units (for GBP, pence).

## Environment
Copy `.env.example` to `.env`. Keep `.env` out of Git. Configure the SQLite path, SMTP variables and the Stripe secret key there.

## Products
The included Northstar catalogue is demonstration content. The first request creates missing products in SQLite from the config. Product names, descriptions, prices and stock are resolved server-side. Each configured product can reference a local image asset under `public/`. There is intentionally no admin panel or database catalogue editor in this starter.

## Checkout
The customer submits name and email, the server creates a pending order, and the app creates a hosted Stripe Checkout Session. Card entry stays on Stripe. After a successful card payment, the customer returns with the Checkout Session ID; the server retrieves that session using `STRIPE_SECRET_KEY` and verifies the paid status, local order reference, amount and currency before marking the order paid.

## Shipping and tax
The demonstration store shows free UK delivery and collects a UK shipping address. Tax, shipping rates, inventory reservation, refunds and fulfilment rules remain business-specific production configuration.

## Contact email
The contact form and order confirmations use PHPMailer over SMTP. See `docs/EMAIL.md`.

## Security
The starter includes CSRF tokens, a honeypot field, short contact throttling, output escaping, security headers and production-safe error handling. Review these controls and add business-specific protections before production use.
