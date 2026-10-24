// @vitest-environment happy-dom
import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonRankAudit from './PersonRankAudit.vue'
import type { RankAuditEntry } from '../../api/personRankAudit'

const { getRankAudit } = vi.hoisted(() => ({
    getRankAudit: vi.fn(),
}))
vi.mock('../../api/personRankAudit', () => ({
    getRankAudit,
}))
const entry: RankAuditEntry = {
    id: '1',
    personId: 7,
    trigger: 'manual',
    created: { at: '2026-10-24T12:00:00+00:00', by: '42' },
    applied: {
        rankId: 7,
        startedOn: '2026-01-01',
        activatedOn: '2026-01-01',
        finishedOn: '2028-01-01',
        nextActivationOn: null,
    },
}
function render() {
    return mount(PersonRankAudit, {
        props: {
            personId: '7',
            refresh: 0,
            rankLabel: (id: number) => (id === 7 ? 'КМС' : 'I'),
        },
        global: {
            stubs: {
                RouterLink: { template: '<a><slot /></a>' },
                SlicePaginator: true,
                Button: {
                    template:
                        '<button @click="$emit(\'click\')"><slot /></button>',
                },
            },
        },
    })
}
describe('rank audit', () => {
    beforeEach(() => {
        vi.resetAllMocks()
        getRankAudit.mockResolvedValue({
            data: [entry],
            pagination: { currentPage: 1, perPage: 20, hasNext: true },
        })
    })
    it('shows the trigger, author and applied rank dates', async () => {
        const wrapper = render()
        await flushPromises()
        expect(wrapper.text()).toContain('КМС')
        expect(wrapper.text()).toContain('Карыстальнік #42')
        expect(wrapper.text()).toContain('Ручны пераразлік')
        expect(wrapper.text()).toContain('2028-01-01')
        expect(wrapper.find('pre').exists()).toBe(false)
        wrapper.unmount()
    })
    it('keeps repeated applications as separate entries in the chain', async () => {
        getRankAudit.mockResolvedValue({
            data: [entry, { ...entry, id: '2', trigger: 'activation' }],
            pagination: { currentPage: 1, perPage: 20, hasNext: false },
        })
        const wrapper = render()
        await flushPromises()
        expect(wrapper.findAll('details')).toHaveLength(2)
        expect(wrapper.text()).toContain('Змена актывацыі')
        expect(
            wrapper.findAll('dd').filter((node) => node.text() === 'КМС'),
        ).toHaveLength(2)
        wrapper.unmount()
    })

    it('ignores an outdated response after selecting another person', async () => {
        let finishFirst!: (value: unknown) => void
        getRankAudit.mockImplementationOnce(
            () =>
                new Promise((resolve) => {
                    finishFirst = resolve
                }),
        )
        const wrapper = render()
        await wrapper.setProps({ personId: '8' })
        await flushPromises()
        finishFirst({
            data: [],
            pagination: { currentPage: 1, perPage: 20, hasNext: false },
        })
        await flushPromises()
        expect(wrapper.text()).toContain('КМС')
        expect(wrapper.findAll('details')).toHaveLength(1)
        wrapper.unmount()
    })

    it('paginates and reloads when the selected person changes', async () => {
        const wrapper = render()
        await flushPromises()
        wrapper
            .findComponent({ name: 'SlicePaginator' })
            .vm.$emit('page', { page: 1, rows: 10 })
        await flushPromises()
        expect(getRankAudit).toHaveBeenLastCalledWith('7', 2, 10)
        await wrapper.setProps({ personId: '8' })
        await flushPromises()
        expect(getRankAudit).toHaveBeenLastCalledWith('8', 1, 20)
        wrapper.unmount()
    })
    it('distinguishes empty coverage from load failure and supports retry', async () => {
        getRankAudit.mockRejectedValueOnce(new Error('network'))
        const wrapper = render()
        await flushPromises()
        expect(wrapper.text()).toContain('Не ўдалося')
        await wrapper.findAll('button').at(-1)!.trigger('click')
        await flushPromises()
        expect(wrapper.text()).toContain('КМС')
        getRankAudit.mockResolvedValue({
            data: [],
            pagination: { currentPage: 1, perPage: 20, hasNext: false },
        })
        await wrapper.setProps({ refresh: 1 })
        await flushPromises()
        expect(wrapper.text()).toContain('Запісаў пакуль няма')
        wrapper.unmount()
    })
})
