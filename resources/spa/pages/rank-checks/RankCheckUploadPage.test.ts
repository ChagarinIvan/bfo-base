// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import RankCheckUploadPage from './RankCheckUploadPage.vue'

const { createRankCheck, push } = vi.hoisted(() => ({
    createRankCheck: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/rankChecks', () => ({ createRankCheck }))
vi.mock('vue-router', () => ({ useRouter: () => ({ push }) }))

const stubs = {
    Button: {
        props: ['disabled'],
        template: '<button :disabled="disabled"><slot /></button>',
    },
    Card: {
        template: '<div><slot name="title" /><slot name="content" /></div>',
    },
    FileUpload: {
        template:
            '<button id="choose-list" @click="$emit(\'select\', { files: [{ name: \'ranks.csv\' }] })">choose</button>',
    },
    Message: { template: '<div role="alert"><slot /></div>' },
}

describe('rank check upload page', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('uses the shared form and file upload controls', async () => {
        const wrapper = mount(RankCheckUploadPage, { global: { stubs } })

        expect(wrapper.find('form.spa-form').exists()).toBe(true)
        expect(wrapper.find('#choose-list').exists()).toBe(true)

        await wrapper.find('#choose-list').trigger('click')
        expect(wrapper.text()).toContain('ranks.csv')
    })

    it('submits the selected list and opens its result', async () => {
        createRankCheck.mockResolvedValue({ id: '42' })
        const wrapper = mount(RankCheckUploadPage, { global: { stubs } })

        await wrapper.find('#choose-list').trigger('click')
        await wrapper.find('form').trigger('submit')

        expect(createRankCheck).toHaveBeenCalledWith(
            expect.objectContaining({ name: 'ranks.csv' }),
        )
        expect(push).toHaveBeenCalledWith('/app/rank-checks')
    })
})
