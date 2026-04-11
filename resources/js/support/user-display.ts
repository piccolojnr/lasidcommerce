type NameLikeUser = {
    name?: string | null;
};

export function getUserDisplayName(user?: NameLikeUser | null): string {
    if (!user) {
        return '';
    }

    const fullName = typeof user.name === 'string' ? user.name.trim() : '';

    if (fullName.length > 0) {
        return fullName;
    }

    return typeof user.name === 'string' ? user.name : '';
}
