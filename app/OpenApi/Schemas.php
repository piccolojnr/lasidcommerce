<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Components(
    schemas: [
        new OA\Schema(
            schema: 'ApiObjectResponse',
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', nullable: true, example: null),
                new OA\Property(property: 'data', type: 'object', nullable: true),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
            ],
            type: 'object'
        ),
        new OA\Schema(
            schema: 'ApiCollectionResponse',
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', nullable: true, example: null),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
            ],
            type: 'object'
        ),
        new OA\Schema(
            schema: 'ApiPaginatedResponse',
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', nullable: true, example: null),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
                new OA\Property(
                    property: 'meta',
                    properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(property: 'from', type: 'integer', nullable: true, example: 1),
                        new OA\Property(property: 'last_page', type: 'integer', example: 5),
                        new OA\Property(property: 'path', type: 'string', example: 'https://example.com/api/v1/catalog/products'),
                        new OA\Property(property: 'per_page', type: 'integer', example: 20),
                        new OA\Property(property: 'to', type: 'integer', nullable: true, example: 20),
                        new OA\Property(property: 'total', type: 'integer', example: 100),
                    ],
                    type: 'object'
                ),
            ],
            type: 'object'
        ),
        new OA\Schema(
            schema: 'ApiErrorResponse',
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string'))
                ),
            ],
            type: 'object'
        ),
        new OA\Schema(
            schema: 'CartItemRequest',
            required: ['product_id', 'quantity'],
            properties: [
                new OA\Property(property: 'product_id', type: 'integer', example: 1),
                new OA\Property(property: 'product_variant_id', type: 'integer', nullable: true, example: 5),
                new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 2),
            ],
            type: 'object'
        ),
    ],
    parameters: [
        new OA\Parameter(
            parameter: 'AcceptJsonHeader',
            name: 'Accept',
            in: 'header',
            required: false,
            schema: new OA\Schema(type: 'string', default: 'application/json'),
            example: 'application/json'
        ),
        new OA\Parameter(
            parameter: 'CartTokenHeader',
            name: 'X-Cart-Token',
            in: 'header',
            required: false,
            description: 'Guest cart token. Authenticated customers may omit this header.',
            schema: new OA\Schema(type: 'string'),
            example: 'cart_01HZ...'
        ),
        new OA\Parameter(
            parameter: 'SearchQuery',
            name: 'search',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string'),
            example: 'shirt'
        ),
        new OA\Parameter(
            parameter: 'SortQuery',
            name: 'sort',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['latest', 'oldest', 'price_asc', 'price_desc', 'name_asc', 'name_desc', 'popular']),
            example: 'latest'
        ),
    ]
)]
final class Schemas
{
}
