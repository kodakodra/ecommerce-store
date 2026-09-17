# Stripe setup

The store uses Stripe Checkout through the `stripe/stripe-php` Composer package. The application connects to Stripe from PHP using the server-side secret key.

Configure `.env` with a Stripe test secret during development:

```text
STRIPE_SECRET_KEY=sk_test_...
```

There is no Stripe webhook endpoint or webhook signing secret in this project.

The checkout flow is:

1. The server calculates the basket from product identifiers and configured prices.
2. A pending order is stored locally.
3. A Stripe Checkout Session is created using server-side line-item amounts.
4. The customer completes the card payment on Stripe Checkout.
5. Stripe sends the customer back to `/checkout/success` with the Checkout Session ID.
6. The server retrieves that Checkout Session using `STRIPE_SECRET_KEY` and verifies the paid status, order reference, amount and currency before marking the local order as paid.
7. A confirmation email is sent after the order is confirmed.

The browser never receives the Stripe secret key. Do not put it in client-side JavaScript or commit it to Git.

This implementation intentionally enables card payments only. Because there is no webhook, a payment that completes without the customer returning to the success URL cannot be confirmed by the application automatically. For this reusable foundation, the customer return is the payment-confirmation step.

For local development, use Stripe test mode. Replace the test secret with a production secret only when the application, fulfilment rules and legal requirements are ready.
