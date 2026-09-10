import { api } from './client'

export async function sendRegistrationInvitation(email: string): Promise<void> {
    await api.post('/auth/registration-invitations', { email })
}

export async function activateRegistrationInvitation(
    token: string,
): Promise<void> {
    await api.post(`/auth/registration-activation/${encodeURIComponent(token)}`)
}
