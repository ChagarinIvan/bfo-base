// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import { describe, expect, it } from 'vitest'
import PersonPromptForm from './PersonPromptForm.vue'

describe('person prompt form', () => {
    it('prefills the prompt and emits the edited value', async () => {
        const wrapper = mount(PersonPromptForm, {
            props: { initialValue: { prompt: 'existing_prompt' } },
            global: { plugins: [PrimeVue] },
        })

        const input = wrapper.find('input#person-prompt')
        expect((input.element as HTMLInputElement).value).toBe(
            'existing_prompt',
        )

        await input.setValue('updated_prompt')
        await wrapper.find('form').trigger('submit')

        expect(wrapper.emitted('submit')).toEqual([
            [{ prompt: 'updated_prompt' }],
        ])
    })

    it('keeps the save button busy and shows field errors while pending', () => {
        const wrapper = mount(PersonPromptForm, {
            props: {
                pending: true,
                errors: { prompt: 'Увядзіце промпт.' },
            },
            global: { plugins: [PrimeVue] },
        })

        expect(wrapper.text()).toContain('Увядзіце промпт.')
        expect(
            wrapper.find('button[type="submit"]').attributes('disabled'),
        ).toBeDefined()
    })
})
