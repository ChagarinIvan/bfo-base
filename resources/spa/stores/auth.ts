import { defineStore } from 'pinia'
import { api, setBearerToken, setUnauthorizedHandler } from '../api/client'
import type { AuthToken } from '../api/types'

const tokenKey = 'auth_token'
let storageListenerRegistered = false

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem(tokenKey) as string | null,
        canAccessHorizon: false,
    }),

    getters: { isAuthenticated: (state) => Boolean(state.token) },

    actions: {
        async login(email: string, password: string): Promise<void> {
            const response = await api.post<AuthToken>('/auth/login', {
                email,
                password,
            })
            this.token = response.data.token
            localStorage.setItem(tokenKey, this.token)
            setBearerToken(this.token)
            await this.refreshHorizonAccess()
        },

        async logout(): Promise<void> {
            try {
                if (this.token) await api.delete('/auth/logout')
            } finally {
                this.token = null
                this.canAccessHorizon = false
                localStorage.removeItem(tokenKey)
                setBearerToken(null)
            }
        },

        async openHorizon(): Promise<void> {
            await api.post('/auth/horizon-session')
            window.location.assign('/horizon/')
        },

        async refreshHorizonAccess(): Promise<void> {
            try {
                const response = await api.get<{ allowed: boolean }>(
                    '/auth/horizon-access',
                )
                this.canAccessHorizon = response.data.allowed
            } catch {
                this.canAccessHorizon = false
            }
        },

        async hydrate(): Promise<void> {
            setUnauthorizedHandler(() => {
                this.token = null
                this.canAccessHorizon = false
                localStorage.removeItem(tokenKey)
            })
            setBearerToken(this.token)

            if (!storageListenerRegistered && typeof window !== 'undefined') {
                window.addEventListener('storage', (event) => {
                    if (event.key !== tokenKey) return

                    this.token = event.newValue
                    setBearerToken(this.token)
                    this.canAccessHorizon = false

                    if (this.token) {
                        void this.refreshHorizonAccess()
                    }
                })
                storageListenerRegistered = true
            }

            if (!this.token) return

            await this.refreshHorizonAccess()
        },
    },
})
