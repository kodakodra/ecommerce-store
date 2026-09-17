# Email setup

Contact messages and paid-order confirmations are sent with PHPMailer over SMTP.

Configure in `.env`:

```text
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=hello@example.com
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_AUTH=1
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="Example Store"
MAIL_TIMEOUT=15
CONTACT_EMAIL=hello@example.com
```

For Gmail, use `smtp.gmail.com` with port `587` and TLS, and use an app password where required. Never commit `.env`.

A mail failure is logged server-side without exposing SMTP details to the visitor. Order payment is not rolled back because an email transport failure must not turn a successful payment into a failed order response.
