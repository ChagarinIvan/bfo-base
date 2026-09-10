// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import SetProtocolLinePersonPage from './SetProtocolLinePersonPage.vue'

const { getPersons, back, setProtocolLinePerson } = vi.hoisted(() => ({
    getPersons: vi.fn(),
    back: vi.fn(),
    setProtocolLinePerson: vi.fn(),
}))

vi.mock('../../api/persons', () => ({ getPersons }))
vi.mock('../../api/protocolLines', () => ({ setProtocolLinePerson }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { protocolLineId: '1470' } }),
    useRouter: () => ({ back }),
}))

const SelectStub = {
    props: ['modelValue', 'options'],
    emits: ['filter', 'update:modelValue'],
    template:
        '<input @input="$emit(\'filter\', { value: $event.target.value })" />',
}

describe('set protocol line person page', () => {
    beforeEach(() => vi.resetAllMocks())

    it('loads people only after entering a searchable name', async () => {
        getPersons.mockResolvedValue({
            data: [
                {
                    id: '7',
                    lastname: 'Ivanov',
                    firstname: 'Ivan',
                    birthday: null,
                    rankId: 0,
                    citizenship: 'belarus',
                    clubId: null,
                },
            ],
            headers: {},
        })

        const wrapper = mount(SetProtocolLinePersonPage, {
            global: {
                stubs: {
                    Button: true,
                    Card: {
                        template:
                            '<div><slot name="title" /><slot name="content" /></div>',
                    },
                    Message: true,
                    Select: SelectStub,
                },
            },
        })

        expect(getPersons).not.toHaveBeenCalled()

        await wrapper.find('input').setValue('Iv')
        expect(getPersons).not.toHaveBeenCalled()

        await wrapper.find('input').setValue('Iva')
        await flushPromises()

        expect(getPersons).toHaveBeenCalledWith({ name: 'Iva', perPage: 20 })
        expect(wrapper.findComponent(SelectStub).props('options')).toHaveLength(
            1,
        )
    })
})
