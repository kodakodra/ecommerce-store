# Stripe setup

The store uses Stripe Checkout through the official `stripe/stripe-php` Composer package. The current stable major line is v21, and this project allows compatible 21.x releases.

Configure `.env` with test credentials during development:

```text
STRIPE_SECRET_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

The checkout flow is:

1. The server calculates the basket from product identifiers and configured prices.
2. A pending order is stored locally.
3. A Stripe Checkout Session is created using server-side line-item amounts.
4. Stripe sends the customer back to `/checkout/success` or `/checkout/cancel`.
5. The signed `/webhooks/stripe` endpoint receives payment events.
6. The webhook changes the order to `paid` and can send the confirmation email.

The success page never marks an order as paid. Keep the webhook endpoint reachable over HTTPS in production and configure its signing secret in the environment.

For local development, use Stripe's test mode and a local webhook forwarding tool. Replace all test credentials with production credentials only when the application, fulfilment rules and legal requirements are ready.
