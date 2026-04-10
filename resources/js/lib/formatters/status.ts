export function formatStatus(status: string | null | undefined): string {
    if (!status) {
        return 'Unknown';
    }

    return status
        .replace(/[_-]+/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .replace(/\b\w/g, (character) => character.toUpperCase());
}
