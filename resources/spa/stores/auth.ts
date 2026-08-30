import { defineStore } from 'pinia'
import { api, setBearerToken, setUnauthorizedHandler } from '../api/client'
import type { AuthToken } from '../api/types'

const tokenKey = 'auth_token'
let storageListenerRegistered = false

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem(tokenKey) as string | null,
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
        },

        async logout(): Promise<void> {
            try {
                if (this.token) await api.delete('/auth/logout')
            } finally {
                this.token = null
                localStorage.removeItem(tokenKey)
                setBearerToken(null)
            }
        },

        async hydrate(): Promise<void> {
            setUnauthorizedHandler(() => {
                this.token = null
                localStorage.removeItem(tokenKey)
            })
            setBearerToken(this.token)

            if (!storageListenerRegistered && typeof window !== 'undefined') {
                window.addEventListener('storage', (event) => {
                    if (event.key !== tokenKey) return

                    this.token = event.newValue
                    setBearerToken(this.token)
                })
                storageListenerRegistered = true
            }

            if (!this.token) return
        },
    },
})
