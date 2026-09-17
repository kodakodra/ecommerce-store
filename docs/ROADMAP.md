# Ecommerce store roadmap

## Foundation
- [x] PHP 8.2+ / Composer project structure
- [x] Environment configuration and secret handling
- [x] Responsive storefront and shared layout
- [x] Configuration-driven product catalogue
- [x] Session cart with server-side pricing
- [x] SQLite order persistence
- [x] Stripe Checkout integration using the server-side secret key
- [x] Payment confirmation on the Stripe Checkout success return
- [x] PHPMailer SMTP contact/order email
- [x] CSRF protection and form validation
- [x] Security response headers and safe error handling
- [x] Smoke tests and GitHub Actions
- [x] Documentation and MIT licence

## Store-specific refinement
- [x] Finalise the demonstration catalogue and product copy
- [x] Add local product image assets
- [x] Define demonstration shipping as free UK delivery and document production-specific tax/fulfilment work
- [x] Document production Stripe secret-key configuration
- [x] Test the successful checkout flow end-to-end
- [x] Provide and document the cancelled-checkout route
- [x] Review final metadata, legal starter pages and deployment configuration

The hosted Stripe Checkout UI did not expose a usable cancellation control during local testing, so the cancellation route is implemented and documented but was not manually click-tested.

## Explicitly out of scope
- Stripe webhooks and webhook signing secrets
- Admin/CMS panel
- Customer accounts/authentication
- Database-backed catalogue management
- Subscriptions
- Marketplace/multi-vendor functionality
