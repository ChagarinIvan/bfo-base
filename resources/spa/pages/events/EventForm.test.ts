// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import EventForm from './EventForm.vue'

const stubs = {
    Button: { template: '<button><slot /></button>' },
    DateFilter: {
        props: ['modelValue', 'inputId', 'label', 'error'],
        template: '<input :id="inputId" :value="modelValue" />',
    },
    FileUpload: {
        template:
            '<button id="choose-protocol" @click="$emit(\'select\', { files: [{ name: \'stage.xlsx\' }] })">choose</button>',
    },
    InputText: {
        props: ['modelValue'],
        template: '<input :value="modelValue" />',
    },
    Message: { template: '<div><slot /></div>' },
    SelectButton: {
        props: ['modelValue', 'options'],
        template:
            '<div><button id="select-file" @click="$emit(\'update:modelValue\', \'file\')">file</button><button id="select-url" @click="$emit(\'update:modelValue\', \'url\')">url</button></div>',
    },
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

    it('selecting a protocol submits only the file source', async () => {
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

        await wrapper.find('#select-file').trigger('click')
        await wrapper.find('#choose-protocol').trigger('click')
        await wrapper.find('form').trigger('submit')

        expect(wrapper.emitted('submit')?.[0]?.[0]).toMatchObject({
            name: 'Stage',
            url: '',
            protocol: { name: 'stage.xlsx' },
        })
    })

    it('submits only the selected URL source', async () => {
        const wrapper = mount(EventForm, {
            props: {
                submitLabel: 'Create',
                sourceRequired: true,
                initialValue: {
                    name: 'Stage',
                    description: 'Description',
                    date: '2026-05-10',
                    protocol: { name: 'stage.xlsx' } as File,
                    url: 'https://obelarus.net/protocol',
                },
            },
            global: { stubs },
        })

        await wrapper.find('#select-url').trigger('click')
        await wrapper.find('form').trigger('submit')

        expect(wrapper.emitted('submit')?.[0]?.[0]).toMatchObject({
            protocol: null,
            url: 'https://obelarus.net/protocol',
        })
    })
})
