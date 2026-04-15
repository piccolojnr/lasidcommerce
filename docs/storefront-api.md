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
- Customer auth/session endpoints for the external shop are still pending and should be implemented before production rollout.
