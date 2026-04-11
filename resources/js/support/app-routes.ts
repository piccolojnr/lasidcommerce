import {
    queryParams
    
    
} from '@/wayfinder';
import type {RouteDefinition, RouteQueryOptions} from '@/wayfinder';

const makeGetRoute = (
    path: string,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: path + queryParams(options),
    method: 'get',
});

export const home = (options?: RouteQueryOptions) => makeGetRoute('/', options);

export const dashboard = (options?: RouteQueryOptions) =>
    makeGetRoute('/admin/dashboard', options);
