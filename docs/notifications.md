# Customer Notifications

This backend sends customer-facing email notifications for auth, order, payment, and shipment events.

## Delivery Model

- All customer notifications are queued Laravel notifications.
- Local development can use `MAIL_MAILER=log` to inspect rendered emails in the application log.
- Production should use a real mail transport and a running queue worker.

## Required Environment

Core mail settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="orders@example.com"
MAIL_FROM_NAME="${APP_NAME}"
INTERNAL_NOTIFICATION_EMAILS=ops@example.com,warehouse@example.com
```

Queue settings:

```env
QUEUE_CONNECTION=database
```

Storefront links used in customer emails:

```env
STOREFRONT_URL=http://storefront.lasidcommerce.test
STOREFRONT_DEFAULT_REDIRECT_PATH=/account
STOREFRONT_ORDERS_PATH=/account/orders
STOREFRONT_AUTH_ERROR_REDIRECT_PATH=/auth
STOREFRONT_MAGIC_LINK_EXPIRE_MINUTES=30
```

## Local Setup

1. Set `MAIL_MAILER=log` if you only need to inspect emails in the logs.
2. If you want to receive real emails locally, point `MAIL_MAILER=smtp` to Mailpit, Mailhog, or another local SMTP server.
3. Run queue workers so queued notifications are delivered:

```bash
php artisan queue:work
```

If you are using the database queue driver, make sure the queue tables exist:

```bash
php artisan queue:table
php artisan queue:failed-table
php artisan migrate
```

## Production Setup

1. Set a real mail transport such as SMTP, SES, Postmark, or Resend.
2. Set a customer-facing sender address in `MAIL_FROM_ADDRESS`.
3. Run a persistent queue worker or Horizon-equivalent process.
4. Monitor failed jobs and mail provider delivery failures.

## Notification Map

### Auth

- `CustomerMagicLinkNotification`
  - Trigger: magic-link request
  - Source: [app/Domain/Auth/Actions/RequestCustomerMagicLinkAction.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Auth/Actions/RequestCustomerMagicLinkAction.php)
  - CTA: one-time sign-in link

- `CustomerVerifyEmailNotification`
  - Trigger: customer verification email send
  - Source: [app/Models/User.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Models/User.php)
  - CTA: Laravel signed verification route

- `CustomerResetPasswordNotification`
  - Trigger: customer password reset request
  - Source: [app/Models/User.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Models/User.php)
  - CTA: Laravel password reset route

- `CustomerWelcomeNotification`
  - Trigger: first successful magic-link verification for a newly created customer
  - Source: [app/Domain/Auth/Actions/VerifyCustomerMagicLinkAction.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Auth/Actions/VerifyCustomerMagicLinkAction.php)
  - CTA: storefront account page

### Orders

- `OrderPlacedNotification`
  - Trigger: order creation from checkout
  - Source: [app/Domain/Checkout/Actions/CreateOrderFromCartAction.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Checkout/Actions/CreateOrderFromCartAction.php)
  - CTA: storefront orders page

- `OrderStatusUpdatedNotification`
  - Trigger: manual order status transition
  - Source: [app/Domain/Order/Actions/UpdateOrderStatusAction.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Order/Actions/UpdateOrderStatusAction.php)
  - CTA: storefront orders page

### Payments

- `PaymentActionRequiredNotification`
  - Trigger: new Paystack payment initialization
  - Source: [app/Domain/Payment/Actions/InitializePaystackPaymentAction.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Payment/Actions/InitializePaystackPaymentAction.php)
  - CTA: Paystack authorization URL
  - Note: not resent when an existing pending payment attempt is reused

- `PaymentReceivedNotification`
  - Trigger: successful payment status synchronization
  - Source: [app/Domain/Payment/Services/PaymentStatusSynchronizer.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Payment/Services/PaymentStatusSynchronizer.php)
  - CTA: storefront orders page

### Shipments

- `ShipmentStatusUpdatedNotification`
  - Trigger: shipment status transition
  - Source: [app/Domain/Shipment/Actions/UpdateShipmentStatusAction.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Shipment/Actions/UpdateShipmentStatusAction.php)
  - CTA: tracking URL when available, otherwise storefront orders page

## Internal Notification Map

Internal recipients are configured through `INTERNAL_NOTIFICATION_EMAILS`.

- `InternalNewCustomerNotification`
  - Trigger: first successful magic-link verification for a newly created customer
  - Source: [app/Domain/Auth/Actions/VerifyCustomerMagicLinkAction.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Auth/Actions/VerifyCustomerMagicLinkAction.php)

- `InternalOrderPlacedNotification`
  - Trigger: order creation from checkout
  - Source: [app/Domain/Checkout/Actions/CreateOrderFromCartAction.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Checkout/Actions/CreateOrderFromCartAction.php)

- `InternalPaymentReceivedNotification`
  - Trigger: successful payment status synchronization
  - Source: [app/Domain/Payment/Services/PaymentStatusSynchronizer.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Payment/Services/PaymentStatusSynchronizer.php)

- `InternalOrderStatusUpdatedNotification`
  - Trigger: manual order status transition
  - Source: [app/Domain/Order/Actions/UpdateOrderStatusAction.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Order/Actions/UpdateOrderStatusAction.php)

- `InternalShipmentStatusUpdatedNotification`
  - Trigger: shipment status transition
  - Source: [app/Domain/Shipment/Actions/UpdateShipmentStatusAction.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Shipment/Actions/UpdateShipmentStatusAction.php)

## Implementation Notes

- Customer-facing operational notifications are dispatched through [app/Domain/Notification/Services/CustomerNotificationService.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Notification/Services/CustomerNotificationService.php).
- Internal operational notifications are dispatched through [app/Domain/Notification/Services/InternalNotificationService.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Notification/Services/InternalNotificationService.php).
- Customer notification preferences are resolved through [app/Domain/Notification/Services/NotificationPreferenceService.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Domain/Notification/Services/NotificationPreferenceService.php).
- Shared branded email rendering lives in [app/Notifications/CustomerMailNotification.php](C:/Users/USER/projects/ecommerce/lasidcommerce/app/Notifications/CustomerMailNotification.php).
- Shared HTML and text templates live in:
  - [resources/views/mail/customer/notification.blade.php](C:/Users/USER/projects/ecommerce/lasidcommerce/resources/views/mail/customer/notification.blade.php)
  - [resources/views/mail/customer/notification-text.blade.php](C:/Users/USER/projects/ecommerce/lasidcommerce/resources/views/mail/customer/notification-text.blade.php)
- Platform users keep Laravel default verify/reset notifications. Customer-branded auth notifications only apply to users classified as customers.
- Customer opt-outs only affect customer-facing emails. Internal operational alerts still send to `INTERNAL_NOTIFICATION_EMAILS`.

## Storefront Preferences

Customer notification preferences are stored on the user record and exposed via the storefront profile API:

- `GET /api/v1/profile`
- `PATCH /api/v1/profile`

Supported preference keys:

- `auth_magic_link`
- `auth_verify_email`
- `auth_password_reset`
- `auth_welcome`
- `orders_placed`
- `orders_status_updates`
- `payments_action_required`
- `payments_received`
- `shipments_status_updates`

## Verification

The notification flows are covered by feature tests in:

- [tests/Feature/Api/Auth/CustomerAuthTest.php](C:/Users/USER/projects/ecommerce/lasidcommerce/tests/Feature/Api/Auth/CustomerAuthTest.php)
- [tests/Feature/Auth/VerificationNotificationTest.php](C:/Users/USER/projects/ecommerce/lasidcommerce/tests/Feature/Auth/VerificationNotificationTest.php)
- [tests/Feature/Auth/PasswordResetTest.php](C:/Users/USER/projects/ecommerce/lasidcommerce/tests/Feature/Auth/PasswordResetTest.php)
- [tests/Feature/Api/Checkout/CreateOrderTest.php](C:/Users/USER/projects/ecommerce/lasidcommerce/tests/Feature/Api/Checkout/CreateOrderTest.php)
- [tests/Feature/Api/Payments/PaymentInitializeTest.php](C:/Users/USER/projects/ecommerce/lasidcommerce/tests/Feature/Api/Payments/PaymentInitializeTest.php)
- [tests/Feature/Webhooks/PaystackWebhookTest.php](C:/Users/USER/projects/ecommerce/lasidcommerce/tests/Feature/Webhooks/PaystackWebhookTest.php)
- [tests/Feature/Admin/Orders/OrderStatusTest.php](C:/Users/USER/projects/ecommerce/lasidcommerce/tests/Feature/Admin/Orders/OrderStatusTest.php)
- [tests/Feature/Admin/Shipments/ShipmentTest.php](C:/Users/USER/projects/ecommerce/lasidcommerce/tests/Feature/Admin/Shipments/ShipmentTest.php)
