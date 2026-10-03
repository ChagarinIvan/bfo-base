import { onBeforeUnmount, onMounted, type Ref } from 'vue'

export function useStickyTableHeaders(
    root: Ref<HTMLElement | null>,
    controls: Ref<HTMLElement | null>,
): void {
    const headers = new Map<HTMLElement, { offset: number; position: string }>()
    let frame: number | undefined
    let sizes: ResizeObserver | undefined
    let changes: MutationObserver | undefined

    function update(): void {
        frame = undefined
        if (!root.value || !controls.value) return

        const panel = controls.value.getBoundingClientRect()
        const measurements = Array.from(
            root.value.querySelectorAll<HTMLElement>('thead.p-datatable-thead'),
        )
            .map((header) => {
                const table = header.closest('table')
                if (!table) return null

                const bounds = header.getBoundingClientRect()
                const previousOffset = header.classList.contains(
                    'listing-table__header--sticky',
                )
                    ? (headers.get(header)?.offset ?? 0)
                    : 0
                const originalTop = bounds.top - previousOffset
                const maximum = Math.max(
                    0,
                    table.getBoundingClientRect().bottom -
                        bounds.height -
                        originalTop,
                )
                return {
                    header,
                    height: bounds.height,
                    offset: Math.max(
                        0,
                        Math.min(panel.bottom - originalTop, maximum),
                    ),
                }
            })
            .filter((measurement) => measurement !== null)

        const panelTop =
            Number.parseFloat(window.getComputedStyle(controls.value).top) || 0
        const headerHeight = Math.max(
            0,
            ...measurements.map(({ height }) => height),
        )
        root.value.style.setProperty(
            '--listing-table-scroll-offset',
            `${panelTop + panel.height + headerHeight + 16}px`,
        )

        for (const { header, offset } of measurements) {
            if (!headers.has(header)) {
                headers.set(header, {
                    offset: 0,
                    position: header.style.position,
                })
            }
            header.style.position = 'relative'
            header.classList.add('listing-table__header--sticky')
            const state = headers.get(header)!
            state.offset = offset
            header.style.setProperty(
                '--listing-table-header-offset',
                `${offset}px`,
            )
        }
        for (const header of headers.keys()) {
            if (!root.value.contains(header)) headers.delete(header)
        }
    }

    function schedule(): void {
        if (frame === undefined) frame = window.requestAnimationFrame(update)
    }

    onMounted(() => {
        if (!root.value || !controls.value) return

        window.addEventListener('scroll', schedule, { passive: true })
        window.addEventListener('resize', schedule)
        root.value.addEventListener('scroll', schedule, true)
        sizes = new ResizeObserver(schedule)
        sizes.observe(root.value)
        sizes.observe(controls.value)
        changes = new MutationObserver(schedule)
        changes.observe(root.value, {
            childList: true,
            characterData: true,
            subtree: true,
        })
        schedule()
    })

    onBeforeUnmount(() => {
        window.removeEventListener('scroll', schedule)
        window.removeEventListener('resize', schedule)
        root.value?.removeEventListener('scroll', schedule, true)
        sizes?.disconnect()
        changes?.disconnect()
        if (frame !== undefined) window.cancelAnimationFrame(frame)
        for (const [header, state] of headers) {
            header.style.position = state.position
            header.style.removeProperty('--listing-table-header-offset')
            header.classList.remove('listing-table__header--sticky')
        }
        headers.clear()
    })
}
