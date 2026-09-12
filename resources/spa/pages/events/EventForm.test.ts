// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import EventForm from './EventForm.vue'

const stubs = {
    Button: { template: '<button><slot /></button>' },
    FileUpload: {
        template:
            '<button id="choose-protocol" @click="$emit(\'select\', { files: [{ name: \'stage.xlsx\' }] })">choose</button>',
    },
    InputText: {
        props: ['modelValue'],
        template: '<input :value="modelValue" />',
    },
    Message: { template: '<div><slot /></div>' },
    Textarea: {
        props: ['modelValue'],
        template: '<textarea :value="modelValue" />',
    },
}

describe('event form', () => {
    it('requires a protocol source when creating an event', async () => {
        const wrapper = mount(EventForm, {
            props: { submitLabel: 'Create', sourceRequired: true },
            global: { stubs },
        })

        await wrapper.find('form').trigger('submit')

        expect(wrapper.text()).toContain('Дадайце файл пратаколу або спасылку.')
        expect(wrapper.emitted('submit')).toBeUndefined()
    })

    it('selecting a protocol clears the URL and emits the submitted form', async () => {
        const wrapper = mount(EventForm, {
            props: {
                submitLabel: 'Create',
                sourceRequired: true,
                initialValue: {
                    name: 'Stage',
                    description: 'Description',
                    date: '2026-05-10',
                    url: 'https://obelarus.net/protocol',
                },
            },
            global: { stubs },
        })

        await wrapper.find('#choose-protocol').trigger('click')
        await wrapper.find('form').trigger('submit')

        expect(wrapper.emitted('submit')?.[0]?.[0]).toMatchObject({
            name: 'Stage',
            url: '',
            protocol: { name: 'stage.xlsx' },
        })
    })
})
