import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import AppLayout from '../layouts/AppLayout.vue'
import DashboardView from '../views/DashboardView.vue'
import AboutView from '../views/AboutView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { guest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { guest: true }
    },
    {
      path: '/about',
      name: 'about',
      component: AboutView
    },
    {
      path: '/',
      component: AppLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: '/dashboard'
        },
        {
          path: 'dashboard',
          name: 'dashboard',
          component: DashboardView
        },
        {
          path: 'study',
          name: 'study',
          component: () => import('../views/StudyView.vue') // Placeholder
        },
        {
          path: 'decks',
          name: 'decks',
          component: () => import('../views/DeckListView.vue')
        },
        {
          path: 'decks/create',
          name: 'deck-create',
          component: () => import('../views/DeckCreateView.vue')
        },
        {
          path: 'decks/:id',
          name: 'deck-detail',
          component: () => import('../views/DeckDetailView.vue')
        },
        {
          path: 'decks/:deckId/add',
          name: 'card-add',
          component: () => import('../views/CardEditorView.vue')
        },
        {
          path: 'cards/:noteId/edit',
          name: 'card-edit',
          component: () => import('../views/CardEditorView.vue')
        },
        {
          path: 'import',
          name: 'import',
          component: () => import('../views/ImportView.vue')
        },
        {
          path: 'settings',
          name: 'settings',
          component: () => import('../views/SettingsView.vue')
        },
        {
          path: 'info',
          name: 'info',
          component: AboutView
        }
      ]
    }
  ]
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token')

  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else if (to.meta.guest && token) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
