export function formatMoney(
    amountInMinorUnits: number | null | undefined,
    currency = 'GHS',
    locale = 'en-GH',
): string {
    const amount = (amountInMinorUnits ?? 0) / 100;

    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}
