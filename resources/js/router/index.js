import { createRouter, createWebHistory } from 'vue-router'
import auth from '../auth'

// Lazy load (mejor performance)
const DashboardView  = () => import('../pages/DashboardView.vue')
const CitasView      = () => import('../pages/CitasView.vue')
const ClientesView   = () => import('../pages/ClientesView.vue')
const BarberosView   = () => import('../pages/BarberosView.vue')
const ServiciosView  = () => import('../pages/ServiciosView.vue')
const PagosView      = () => import('../pages/PagosView.vue') 
const ReportsView   = () => import('../pages/ReportsView.vue')  

// (opcional) 404
const NotFound       = {
  template: `<div class="p-10 text-center">
               <h1 class="text-2xl font-semibold">404</h1>
               <p class="text-gray-500">Página no encontrada</p>
             </div>`
}

const LoginView = () => import('../pages/LoginView.vue')

const routes = [
  { path: '/login',     name: 'login',     component: LoginView,    meta: { title: 'Login', public: true } },
  { path: '/',          name: 'dashboard', component: DashboardView, meta: { title: 'Dashboard' } },
  { path: '/citas',     name: 'citas',     component: CitasView,     meta: { title: 'Citas' } },
  { path: '/clientes',  name: 'clientes',  component: ClientesView,  meta: { title: 'Clientes' } },
  { path: '/barberos',  name: 'barberos',  component: BarberosView,  meta: { title: 'Barberos' } },
  { path: '/servicios', name: 'servicios', component: ServiciosView, meta: { title: 'Servicios' } },
  { path: '/pagos',     name: 'pagos',     component: PagosView,     meta: { title: 'Pagos' } }, 
 { path: '/reportes', name: 'reportes', component: ReportsView, meta: { title: 'Reportes' } },
  // 404 al final
  { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound, meta: { title: '404' } },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

let initialized = false
router.beforeEach(async (to, from, next) => {
  document.title = `BarberSoft — ${to.meta?.title ?? 'App'}`
  if (!initialized) {
    initialized = true
    await auth.init()
  }
  const isAuthed = auth.isAuthenticated.value
  if (!to.meta?.public && !isAuthed) return next({ name: 'login' })
  if (to.name === 'login' && isAuthed) return next({ name: 'dashboard' })
  next()
})

export default router
