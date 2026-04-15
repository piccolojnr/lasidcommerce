# Storefront API Guide

This backend serves the public shop API under `/api/v1`.

## Core flow

1. Browse catalog
   - `GET /api/v1/catalog/categories`
   - `GET /api/v1/catalog/products`
2. Build cart
   - `GET /api/v1/cart`
   - `POST /api/v1/cart/items`
   - Persist the `X-Cart-Token` header value returned by the cart endpoint for guest sessions.
3. Resolve shipping options
   - `POST /api/v1/checkout/shipping-methods/resolve`
4. Preview checkout
   - `POST /api/v1/checkout/preview`
5. Choose one checkout path
   - Two-step:
     - `POST /api/v1/checkout/orders`
     - `POST /api/v1/payments/initialize`
   - One-step:
     - `POST /api/v1/checkout/initialize`

## Storefront auth and session

Storefront auth is cookie-session based and isolated from the admin web session.

### Bootstrap

1. `GET /api/v1/auth/csrf-cookie`
   - establishes the storefront session cookie
   - returns:
     - `csrf_token`
     - `csrf_cookie`
     - `csrf_header`
2. Send the returned token back on mutating auth/session requests using the header named by `csrf_header`.

### Session endpoints

- `GET /api/v1/auth/session`
  - returns auth state and the current customer payload
- `POST /api/v1/auth/logout`
  - invalidates the storefront session only

### Magic-link auth

- `POST /api/v1/auth/magic-link/request`
  - accepts:
    - `email`
    - optional `cart_token`
    - optional `redirect_to`
  - if the email belongs to an existing customer, a sign-in link is sent
  - if the email does not exist, account creation is deferred until the link is used
  - platform/staff users are not eligible through this surface
- `GET /api/v1/auth/magic-link/verify`
  - consumes the one-time link
  - creates the customer if needed
  - verifies the email automatically
  - logs the customer into the storefront session
  - merges/adopts the guest cart if `cart_token` was provided
  - redirects to the storefront URL configured by `STOREFRONT_URL`

### Password fallback

- `POST /api/v1/auth/password/login`
- `POST /api/v1/auth/password/forgot`
- `POST /api/v1/auth/password/reset`

Password is a fallback. Magic-link-created customers can set a password later through the reset flow.

### Cart continuity

- Guests should persist `X-Cart-Token`
- On successful magic-link or password login, the backend adopts or merges the guest cart into the customer cart automatically
- If both carts exist, quantities are merged by product and variant identity

## Response conventions

- Every response uses the standard envelope:
  - `success`
  - `message`
  - `data`
  - `errors`
- Money values are always stored and returned in minor units.

## Shipping resolution

`POST /api/v1/checkout/shipping-methods/resolve`

Required fields:
- `country`
- `city`

Optional fields:
- `region`
- `cart_id`

Returns:
- `shipping_zone`
- `shipping_methods`

If no zone matches, the API returns success with `shipping_zone = null` and an empty `shipping_methods` array.

## Checkout initialize

`POST /api/v1/checkout/initialize`

This is the one-shot checkout path for the storefront.

Required fields:
- `address_id`
- `shipping_method_id`
- `payment_provider`

Current supported provider:
- `paystack`

Behavior:
- validates cart, address, and shipping method
- creates the order
- initializes the payment session

If order creation succeeds but payment setup fails, the API returns an error response with:
- `errors.order_id`
- `errors.payment`

The storefront should use that `order_id` to retry payment initialization through `POST /api/v1/payments/initialize`.

## Current limitations

- Coupon application endpoints are not ready for storefront use yet.
