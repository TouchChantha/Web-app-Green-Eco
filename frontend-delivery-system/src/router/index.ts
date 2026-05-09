import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // ── Public ──────────────────────────────────────────────────────────────
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { guest: true },
    },

    // ── Authenticated shell ──────────────────────────────────────────────────
    {
      path: '/',
      component: () => import('../layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        // Smart root redirect — resolved in beforeEach
        { path: '', name: 'home', redirect: () => '/dashboard' },

        // ── Admin-only ───────────────────────────────────────────────────────
        {
          path: 'dashboard',
          name: 'dashboard',
          component: () => import('../views/DashboardView.vue'),
          meta: { requiresAuth: true, roles: ['admin'] },
        },
        {
          path: 'orders/create',
          name: 'orders.create',
          component: () => import('../views/orders/OrderCreateView.vue'),
          meta: { requiresAuth: true, roles: ['admin'] },
        },
        {
          path: 'drivers',
          name: 'drivers',
          component: () => import('../views/drivers/DriversView.vue'),
          meta: { requiresAuth: true, roles: ['admin'] },
        },
        {
          path: 'drivers/create',
          name: 'drivers.create',
          component: () => import('../views/drivers/DriverCreateView.vue'),
          meta: { requiresAuth: true, roles: ['admin'] },
        },
        {
          path: 'drivers/:id',
          name: 'drivers.show',
          component: () => import('../views/drivers/DriverDetailView.vue'),
          meta: { requiresAuth: true, roles: ['admin'] },
        },
        {
          path: 'reports',
          name: 'reports',
          component: () => import('../views/ReportsView.vue'),
          meta: { requiresAuth: true, roles: ['admin'] },
        },
        {
          path: 'live-tracking',
          name: 'live-tracking',
          component: () => import('../views/LiveTrackingView.vue'),
          meta: { requiresAuth: true, roles: ['admin'] },
        },

        // ── Shared (admin + driver) ──────────────────────────────────────────
        {
          path: 'orders',
          name: 'orders',
          component: () => import('../views/orders/OrdersView.vue'),
          meta: { requiresAuth: true, roles: ['admin', 'driver'] },
        },
        {
          path: 'orders/:id',
          name: 'orders.show',
          component: () => import('../views/orders/OrderDetailView.vue'),
          meta: { requiresAuth: true, roles: ['admin', 'driver'] },
        },

        // ── Driver-only ──────────────────────────────────────────────────────
        {
          path: 'driver-portal',
          name: 'driver-portal',
          component: () => import('../views/DriverPortalView.vue'),
          meta: { requiresAuth: true, roles: ['driver'] },
        },
      ],
    },

    // Catch-all
    { path: '/:pathMatch(.*)*', redirect: '/login' },
  ],
})

// ── Global navigation guard ────────────────────────────────────────────────
router.beforeEach((to, _from, next) => {
  const auth = useAuthStore()

  // Not logged in → go to login
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login' })
  }

  // Already logged in → skip login page, redirect to home
  if (to.meta.guest && auth.isAuthenticated) {
    return next(auth.isAdmin ? { name: 'dashboard' } : { name: 'driver-portal' })
  }

  // Smart root redirect based on role
  if (to.name === 'home' && auth.isAuthenticated) {
    return next(auth.isAdmin ? { name: 'dashboard' } : { name: 'driver-portal' })
  }

  // Role check — redirect to role's home if accessing a forbidden route
  if (to.meta.roles && auth.user) {
    const allowed = to.meta.roles as string[]
    if (!allowed.includes(auth.user.role)) {
      return next(auth.isAdmin ? { name: 'dashboard' } : { name: 'driver-portal' })
    }
  }

  next()
})

export default router
