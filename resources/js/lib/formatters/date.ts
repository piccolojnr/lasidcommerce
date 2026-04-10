export function formatDate(value: string | Date | null | undefined, locale = 'en-GH'): string {
    if (!value) {
        return 'N/A';
    }

    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return 'Invalid date';
    }

    return new Intl.DateTimeFormat(locale, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
}
