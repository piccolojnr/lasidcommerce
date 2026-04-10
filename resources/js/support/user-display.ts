type NameLikeUser = {
    first_name?: string | null;
    last_name?: string | null;
    name?: string | null;
};

export function getUserDisplayName(user?: NameLikeUser | null): string {
    if (!user) {
        return '';
    }

    const fullName = [user.first_name, user.last_name]
        .filter((value): value is string => typeof value === 'string' && value.trim().length > 0)
        .join(' ')
        .trim();

    if (fullName.length > 0) {
        return fullName;
    }

    return typeof user.name === 'string' ? user.name : '';
}
