export interface ListingColumn {
    key: string
    label: string
    defaultVisible: boolean
    field?: string
    required?: boolean
    configurable?: boolean
}

export function tableStorageKey(
    tableId: string,
    authenticated: boolean,
): string {
    return `bfo.table.${tableId}.${authenticated ? 'authenticated' : 'guest'}`
}

export function sanitizeVisibleColumns(
    saved: readonly string[],
    available: readonly string[],
    defaults: readonly string[],
): string[] {
    const permitted = new Set(available)
    const visible = saved.filter((key) => permitted.has(key))

    return visible.length > 0
        ? [...new Set(visible)]
        : defaults.filter((key) => permitted.has(key))
}

export function protocolLineEventUrl(
    eventId: string,
    protocolLineId: string,
    distanceId?: string,
): string {
    const query = distanceId ? `?distanceId=${distanceId}` : ''

    return `/app/events/${eventId}${query}#protocol-line-${protocolLineId}`
}
