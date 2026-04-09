# AGENTS.md

## Purpose
This project is a Laravel 12 e-commerce backend with two surfaces:

1. **Admin Web App**
   - Internal/admin-facing
   - Uses Laravel web routes
   - Uses session auth
   - Uses roles/permissions
   - Used for catalog, orders, shipping, coupons, settings, users

2. **Public API**
   - Customer-facing
   - Uses Laravel API routes
   - Used by storefront/mobile/client apps
   - Handles catalog, cart, checkout, orders, addresses, profile

This is **one Laravel backend**, not two separate backends.

---

## Core Architectural Rules

### 1. Keep controllers thin
Controllers must only:
- authorize
- validate
- call an action/service
- return a response/resource

Controllers must **not** contain:
- heavy business logic
- pricing logic
- stock logic
- payment verification logic
- order state transition logic
- large query composition

---

### 2. Put business logic in the domain layer
Business logic belongs in:

- `app/Domain/*/Actions`
- `app/Domain/*/Services`
- `app/Domain/*/Queries`
- `app/Domain/*/DTOs`

Use:
- **Actions** for single use cases
- **Services** for reusable domain logic
- **Queries** for non-trivial reads
- **DTOs** for structured data passed into actions/services

Do **not** create giant god services like:
- `OrderService` with 30 unrelated methods
- `ProductService` containing all catalog behavior

Prefer:
- `CreateOrderFromCartAction`
- `CancelOrderAction`
- `ApplyCouponAction`
- `ResolveShippingZoneAction`
- `VerifyPaystackSignatureAction`

---

### 3. Separate by interface and domain
Code must stay clearly split between:
- admin web concerns
- public API concerns
- shared domain logic

Use these boundaries:
- `app/Http/Controllers/Admin/...`
- `app/Http/Controllers/Api/...`
- `app/Http/Controllers/Webhooks/...`

Shared business logic must not be duplicated between Admin and API layers.

---

### 4. Validation belongs in Form Requests
Do not validate request input inline in controllers.

Use:
- `app/Http/Requests/Admin/...`
- `app/Http/Requests/Api/...`

---

### 5. Authorization must use policies + permissions
Use:
- **Spatie Permission** for coarse-grained permissions
- **Laravel Policies** for model/resource-level authorization

Do not hardcode role checks everywhere in controllers.

Examples:
- permission: `manage products`
- policy: whether this user can update this specific product/order/shipment

---

### 6. Responses must be consistent
Public API responses must follow a consistent envelope.

Standard success shape:
```json
{
  "success": true,
  "message": "Request completed successfully.",
  "data": {},
  "meta": {}
}