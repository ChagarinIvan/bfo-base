import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import CompetitionsPage from '../pages/competitions/CompetitionsPage.vue'
import CreateCompetitionPage from '../pages/competitions/CreateCompetitionPage.vue'
import LoginPage from '../pages/auth/LoginPage.vue'

const router = createRouter({
  history: createWebHistory('/'),
  routes: [
    { path: '/app/competitions', component: CompetitionsPage },
    { path: '/app/competitions/create', component: CreateCompetitionPage, meta: { requiresAuth: true } },
    { path: '/app/login', component: LoginPage },
    { path: '/app/:pathMatch(.*)*', redirect: '/app/competitions' },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  await auth.hydrate()
  if (to.meta.requiresAuth && !auth.isAuthenticated) return { path: '/app/login', query: { return: to.fullPath } }
})

export default router
