/**
 * Shared utilities for product create and edit forms.
 * Both ProductForm (edit) and ProductCreateWizard (create) import from here
 * to keep slug, price, and display logic in one place.
 */

export const PRICE_LOCALE = 'en-GH';
export const PRICE_CURRENCY = 'GHS';

/** Convert a raw string into a URL-safe slug. */
export function slugify(value: string): string {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

/** Convert a cents integer to a display decimal string, e.g. 1999 → "19.99". */
export function centsToDisplay(cents: number | null | undefined): string {
    return cents == null ? '' : (cents / 100).toFixed(2);
}

/**
 * Convert a decimal display string back to a cents string for hidden inputs,
 * e.g. "19.99" → "1999". Returns empty string when the display is empty.
 */
export function displayToCents(display: string): string {
    if (!display) {
 return ''; 
}

    const value = Number.parseFloat(display);

    return Number.isNaN(value) ? '0' : String(Math.round(value * 100));
}

/**
 * Format a decimal display string as a localised currency string for preview
 * panels, e.g. "19.99" → "GHS 19.99". Returns "Not set" for empty/invalid input.
 */
export function pricePreview(display: string): string {
    if (!display) {
 return 'Not set'; 
}

    const value = Number.parseFloat(display);

    if (Number.isNaN(value)) {
 return 'Not set'; 
}

    return new Intl.NumberFormat(PRICE_LOCALE, {
        style: 'currency',
        currency: PRICE_CURRENCY,
        minimumFractionDigits: 2,
    }).format(value);
}
