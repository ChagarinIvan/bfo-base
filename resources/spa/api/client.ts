import axios from 'axios'

export const api = axios.create({ baseURL: '/api/v1', headers: { Accept: 'application/json' } })

let unauthorizedHandler: (() => void) | null = null

export function setBearerToken(token: string | null): void {
  if (token) api.defaults.headers.common.Authorization = `Bearer ${token}`
  else delete api.defaults.headers.common.Authorization
}

export function setUnauthorizedHandler(handler: (() => void) | null): void {
  unauthorizedHandler = handler
}

api.interceptors.response.use(undefined, (error) => {
  if (error.response?.status === 401) {
    setBearerToken(null)
    unauthorizedHandler?.()

    if (typeof window !== 'undefined' && window.location.pathname !== '/app/login') {
      void window.location.assign('/app/login')
    }
  }

  return Promise.reject(error)
})
