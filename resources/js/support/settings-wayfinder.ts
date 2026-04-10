import {
    queryParams
} from '@/wayfinder';
import type { RouteDefinition, RouteFormDefinition, RouteQueryOptions } from '@/wayfinder';

const profilePath = '/settings/profile';
const securityPath = '/settings/security';
const appearancePath = '/settings/appearance';
const passwordPath = '/settings/password';

const makeGetRoute = (
    path: string,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: path + queryParams(options),
    method: 'get',
});

const makePatchForm = (
    path: string,
    options?: RouteQueryOptions,
): RouteFormDefinition<'patch'> => ({
    action: path + queryParams(options),
    method: 'patch',
});

const makePutForm = (
    path: string,
    options?: RouteQueryOptions,
): RouteFormDefinition<'put'> => ({
    action: path + queryParams(options),
    method: 'put',
});

const makeDeleteForm = (
    path: string,
    options?: RouteQueryOptions,
): RouteFormDefinition<'delete'> => ({
    action: path + queryParams(options),
    method: 'delete',
});

export const profileEdit = (options?: RouteQueryOptions) =>
    makeGetRoute(profilePath, options);

export const profileUpdateForm = (options?: RouteQueryOptions) =>
    makePatchForm(profilePath, options);

export const profileDestroyForm = (options?: RouteQueryOptions) =>
    makeDeleteForm(profilePath, options);

export const securityEdit = (options?: RouteQueryOptions) =>
    makeGetRoute(securityPath, options);

export const securityUpdateForm = (options?: RouteQueryOptions) =>
    makePutForm(passwordPath, options);

export const appearanceEdit = (options?: RouteQueryOptions) =>
    makeGetRoute(appearancePath, options);
