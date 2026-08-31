import {
    createMemoryHistory,
    createRouter,
    createWebHistory,
    type RouterHistory,
} from 'vue-router'
import { useAuthStore } from '../stores/auth'
import CompetitionsPage from '../pages/competitions/CompetitionsPage.vue'
import CreateCompetitionPage from '../pages/competitions/CreateCompetitionPage.vue'
import CompetitionDetailsPage from '../pages/competitions/CompetitionDetailsPage.vue'
import EditCompetitionPage from '../pages/competitions/EditCompetitionPage.vue'
import ClubsPage from '../pages/clubs/ClubsPage.vue'
import LoginPage from '../pages/auth/LoginPage.vue'

export function createAppRouter(
    history: RouterHistory = typeof window === 'undefined'
        ? createMemoryHistory()
        : createWebHistory('/'),
) {
    const router = createRouter({
        history,
        routes: [
            { path: '/app/competitions', component: CompetitionsPage },
            { path: '/app/clubs', component: ClubsPage },
            {
                path: '/app/competitions/create',
                component: CreateCompetitionPage,
                meta: { requiresAuth: true },
            },
            {
                path: '/app/competitions/:id',
                component: CompetitionDetailsPage,
            },
            {
                path: '/app/competitions/:id/edit',
                component: EditCompetitionPage,
                meta: { requiresAuth: true },
            },
            { path: '/app/login', component: LoginPage },
            { path: '/app/:pathMatch(.*)*', redirect: '/app/competitions' },
        ],
    })

    router.beforeEach(async (to) => {
        const auth = useAuthStore()
        await auth.hydrate()
        if (to.meta.requiresAuth && !auth.isAuthenticated)
            return { path: '/app/login', query: { return: to.fullPath } }
    })

    return router
}

export default createAppRouter()
