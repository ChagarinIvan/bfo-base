// @vitest-environment happy-dom
import { mount, flushPromises } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import ManualPersonRankDialog from './ManualPersonRankDialog.vue'
const { addManualPersonRank } = vi.hoisted(() => ({
    addManualPersonRank: vi.fn(),
}))
vi.mock('../api/personRankHistory', () => ({ addManualPersonRank }))
function dialog() {
    return mount(ManualPersonRankDialog, {
        props: {
            personId: '7',
            ranks: [
                { id: 0, label: 'б/р' },
                { id: 6, label: 'I' },
            ],
        },
        global: {
            stubs: {
                Dialog: { template: '<div><slot /></div>' },
                Select: {
                    props: ['modelValue', 'options', 'inputId'],
                    emits: ['update:modelValue'],
                    template:
                        '<select :id="inputId" :value="modelValue" @change="$emit(\'update:modelValue\', Number($event.target.value))"><option value="">Select</option><option v-for="o in options" :key="o.id" :value="o.id">{{ o.label }}</option></select>',
                },
                Button: {
                    props: ['label', 'type'],
                    template:
                        '<button :type="type || \'button\'" @click="$emit(\'click\')">{{ label }}</button>',
                },
                Message: { template: '<div role="alert"><slot /></div>' },
            },
        },
    })
}
async function fill(wrapper: ReturnType<typeof dialog>) {
    await wrapper.get('select').setValue('6')
    await wrapper.get('#manual-rank-achieved').setValue('2026-01-01')
    await wrapper.get('#manual-rank-activated').setValue('2026-01-10')
}
describe('manual rank dialog', () => {
    beforeEach(() => vi.resetAllMocks())
    it('validates required fields and excludes without rank', async () => {
        const wrapper = dialog()
        expect(wrapper.findAll('option').map((o) => o.text())).not.toContain(
            'б/р',
        )
        await wrapper.get('form').trigger('submit')
        expect(addManualPersonRank).not.toHaveBeenCalled()
        expect(wrapper.find('[role="alert"]').exists()).toBe(true)
    })
    it('preserves entered values after an API failure', async () => {
        addManualPersonRank.mockRejectedValue(new Error('409'))
        const wrapper = dialog()
        await fill(wrapper)
        await wrapper.get('form').trigger('submit')
        await flushPromises()
        expect(addManualPersonRank).toHaveBeenCalledWith('7', {
            rankId: 6,
            achievedOn: '2026-01-01',
            activatedOn: '2026-01-10',
        })
        expect(
            (wrapper.get('#manual-rank-achieved').element as HTMLInputElement)
                .value,
        ).toBe('2026-01-01')
        expect(wrapper.find('[role="alert"]').exists()).toBe(true)
        expect(wrapper.emitted('saved')).toBeUndefined()
    })
    it('blocks duplicate submits and ignores completion after unmount', async () => {
        let resolve!: () => void
        addManualPersonRank.mockReturnValue(
            new Promise<void>((done) => {
                resolve = done
            }),
        )
        const wrapper = dialog()
        await fill(wrapper)
        await wrapper.get('form').trigger('submit')
        await wrapper.get('form').trigger('submit')
        expect(addManualPersonRank).toHaveBeenCalledOnce()
        wrapper.unmount()
        resolve()
        await flushPromises()
        expect(wrapper.emitted('saved')).toBeUndefined()
    })
    it('reports successful save for the captured person', async () => {
        addManualPersonRank.mockResolvedValue(undefined)
        const wrapper = dialog()
        await fill(wrapper)
        await wrapper.get('form').trigger('submit')
        await flushPromises()
        expect(wrapper.emitted('saved')).toEqual([['7']])
    })
})
