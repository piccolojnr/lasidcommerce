<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Catalog', description: 'Public catalog browsing endpoints.')]
#[OA\Tag(name: 'Cart', description: 'Guest and customer cart endpoints.')]
#[OA\Tag(name: 'Checkout', description: 'Checkout helper endpoints.')]
final class ApiRoutes
{
    #[OA\Get(
        path: '/api/v1/catalog/brands',
        operationId: 'listBrands',
        tags: ['Catalog'],
        summary: 'List brands',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Brands returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiCollectionResponse')),
        ]
    )]
    public function listBrands(): void {}

    #[OA\Get(
        path: '/api/v1/catalog/brands/{slug}',
        operationId: 'showBrand',
        tags: ['Catalog'],
        summary: 'Show a brand',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'nike'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Brand returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
            new OA\Response(response: 404, description: 'Brand not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function showBrand(): void {}

    #[OA\Get(
        path: '/api/v1/catalog/categories',
        operationId: 'listCategories',
        tags: ['Catalog'],
        summary: 'List categories',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(name: 'root_only', in: 'query', required: false, schema: new OA\Schema(type: 'boolean'), example: true),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Categories returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiCollectionResponse')),
        ]
    )]
    public function listCategories(): void {}

    #[OA\Get(
        path: '/api/v1/catalog/categories/{slug}',
        operationId: 'showCategory',
        tags: ['Catalog'],
        summary: 'Show a category',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'sneakers'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Category returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
            new OA\Response(response: 404, description: 'Category not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function showCategory(): void {}

    #[OA\Get(
        path: '/api/v1/catalog/tags',
        operationId: 'listTags',
        tags: ['Catalog'],
        summary: 'List tags',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tags returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiCollectionResponse')),
        ]
    )]
    public function listTags(): void {}

    #[OA\Get(
        path: '/api/v1/catalog/tags/{slug}',
        operationId: 'showTag',
        tags: ['Catalog'],
        summary: 'Show a tag with products',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'new-arrivals'),
            new OA\Parameter(ref: '#/components/parameters/SearchQuery'),
            new OA\Parameter(ref: '#/components/parameters/SortQuery'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tag and products returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
            new OA\Response(response: 404, description: 'Tag not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function showTag(): void {}

    #[OA\Get(
        path: '/api/v1/catalog/collections',
        operationId: 'listCollections',
        tags: ['Catalog'],
        summary: 'List collections',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Collections returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiCollectionResponse')),
        ]
    )]
    public function listCollections(): void {}

    #[OA\Get(
        path: '/api/v1/catalog/collections/{slug}',
        operationId: 'showCollection',
        tags: ['Catalog'],
        summary: 'Show a collection with products',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'summer-edit'),
            new OA\Parameter(ref: '#/components/parameters/SearchQuery'),
            new OA\Parameter(ref: '#/components/parameters/SortQuery'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Collection and products returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
            new OA\Response(response: 404, description: 'Collection not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function showCollection(): void {}

    #[OA\Get(
        path: '/api/v1/catalog/products',
        operationId: 'listProducts',
        tags: ['Catalog'],
        summary: 'List products',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(ref: '#/components/parameters/SearchQuery'),
            new OA\Parameter(ref: '#/components/parameters/SortQuery'),
            new OA\Parameter(name: 'category', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'sneakers'),
            new OA\Parameter(name: 'brand', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'nike'),
            new OA\Parameter(name: 'tag', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'new-arrivals'),
            new OA\Parameter(name: 'collection', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'summer-edit'),
            new OA\Parameter(name: 'featured', in: 'query', required: false, schema: new OA\Schema(type: 'boolean'), example: true),
            new OA\Parameter(name: 'min_price', in: 'query', required: false, description: 'Minimum price in minor units.', schema: new OA\Schema(type: 'integer'), example: 5000),
            new OA\Parameter(name: 'max_price', in: 'query', required: false, description: 'Maximum price in minor units.', schema: new OA\Schema(type: 'integer'), example: 50000),
            new OA\Parameter(name: 'popularity_period', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: '30d'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Products returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiPaginatedResponse')),
        ]
    )]
    public function listProducts(): void {}

    #[OA\Get(
        path: '/api/v1/catalog/products/{slug}',
        operationId: 'showProduct',
        tags: ['Catalog'],
        summary: 'Show a product',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'air-max-90'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
            new OA\Response(response: 404, description: 'Product not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function showProduct(): void {}

    #[OA\Get(
        path: '/api/v1/cart',
        operationId: 'showCart',
        tags: ['Cart'],
        summary: 'Show the current cart',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(ref: '#/components/parameters/CartTokenHeader'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cart returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
        ]
    )]
    public function showCart(): void {}

    #[OA\Post(
        path: '/api/v1/cart/items',
        operationId: 'storeCartItem',
        tags: ['Cart'],
        summary: 'Add an item to the cart',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(ref: '#/components/parameters/CartTokenHeader'),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CartItemRequest')),
        responses: [
            new OA\Response(response: 201, description: 'Cart item added.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
            new OA\Response(response: 422, description: 'Validation or cart error.', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function storeCartItem(): void {}

    #[OA\Patch(
        path: '/api/v1/cart/items/{cartItem}',
        operationId: 'updateCartItem',
        tags: ['Cart'],
        summary: 'Update a cart item quantity',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(ref: '#/components/parameters/CartTokenHeader'),
            new OA\Parameter(name: 'cartItem', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 10),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['quantity'], properties: [
            new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 2),
        ], type: 'object')),
        responses: [
            new OA\Response(response: 200, description: 'Cart item updated.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
            new OA\Response(response: 404, description: 'Cart item not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Validation or cart error.', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function updateCartItem(): void {}

    #[OA\Delete(
        path: '/api/v1/cart/items/{cartItem}',
        operationId: 'deleteCartItem',
        tags: ['Cart'],
        summary: 'Remove a cart item',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(ref: '#/components/parameters/CartTokenHeader'),
            new OA\Parameter(name: 'cartItem', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 10),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cart item removed.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
            new OA\Response(response: 404, description: 'Cart item not found.', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function deleteCartItem(): void {}

    #[OA\Post(
        path: '/api/v1/cart/coupon',
        operationId: 'applyCartCoupon',
        tags: ['Cart'],
        summary: 'Apply a coupon to the cart',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(ref: '#/components/parameters/CartTokenHeader'),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['code'], properties: [
            new OA\Property(property: 'code', type: 'string', example: 'WELCOME10'),
        ], type: 'object')),
        responses: [
            new OA\Response(response: 200, description: 'Coupon applied.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
            new OA\Response(response: 422, description: 'Validation error.', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function applyCartCoupon(): void {}

    #[OA\Delete(
        path: '/api/v1/cart/coupon',
        operationId: 'removeCartCoupon',
        tags: ['Cart'],
        summary: 'Remove the cart coupon',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
            new OA\Parameter(ref: '#/components/parameters/CartTokenHeader'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Coupon removed.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
        ]
    )]
    public function removeCartCoupon(): void {}

    #[OA\Post(
        path: '/api/v1/checkout/shipping-methods/resolve',
        operationId: 'resolveCheckoutShippingMethods',
        tags: ['Checkout'],
        summary: 'Resolve available shipping methods',
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/AcceptJsonHeader'),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['state'], properties: [
            new OA\Property(property: 'country', type: 'string', example: 'NG'),
            new OA\Property(property: 'state', type: 'string', example: 'Lagos'),
            new OA\Property(property: 'city', type: 'string', example: 'Ikeja'),
            new OA\Property(property: 'postal_code', type: 'string', nullable: true, example: '100001'),
        ], type: 'object')),
        responses: [
            new OA\Response(response: 200, description: 'Shipping methods returned.', content: new OA\JsonContent(ref: '#/components/schemas/ApiObjectResponse')),
            new OA\Response(response: 422, description: 'Validation error.', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function resolveCheckoutShippingMethods(): void {}
}
