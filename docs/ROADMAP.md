# Ecommerce store roadmap

## Foundation
- [x] PHP 8.2+ / Composer project structure
- [x] Environment configuration and secret handling
- [x] Responsive storefront and shared layout
- [x] Configuration-driven product catalogue
- [x] Session cart with server-side pricing
- [x] SQLite order persistence
- [x] Stripe Checkout integration
- [x] Signed Stripe webhook endpoint
- [x] PHPMailer SMTP contact/order email
- [x] CSRF protection and form validation
- [x] Security response headers and safe error handling
- [x] Smoke tests and GitHub Actions
- [x] Documentation and MIT licence

## Store-specific refinement
- [ ] Replace demo products/content with a real catalogue
- [ ] Add real product images/assets
- [ ] Review shipping, tax and fulfilment rules for the target business
- [ ] Configure production Stripe keys and webhook endpoint
- [ ] Test successful, cancelled and webhook-confirmed checkout flows
- [ ] Review final metadata, legal text and deployment configuration

## Explicitly out of scope
- Admin/CMS panel
- Customer accounts/authentication
- Database-backed catalogue management
- Subscriptions
- Marketplace/multi-vendor functionality
