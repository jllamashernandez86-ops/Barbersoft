<script setup>
import { RouterLink, useRouter } from 'vue-router'
import auth from '../auth'

const router = useRouter()
const logout = async () => {
  await auth.logout()
  router.replace({ name: 'login' })
}
</script>

<template>
  <div class="min-h-screen flex bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-60 bg-white border-r">
      <div class="p-4 text-xl font-bold">BarberSoft</div>
      <div class="px-4 pb-3 text-sm text-gray-600">
        <div class="truncate">{{ auth.state.user?.name || 'Invitado' }}</div>
        <button class="mt-2 text-xs text-red-600 hover:underline" @click="logout">Cerrar sesión</button>
      </div>
      <nav class="px-2 space-y-1">
        <RouterLink class="block px-3 py-2 rounded hover:bg-gray-100" to="/">Dashboard</RouterLink>
        <RouterLink class="block px-3 py-2 rounded hover:bg-gray-100" to="/citas">Citas</RouterLink>
        <RouterLink class="block px-3 py-2 rounded hover:bg-gray-100" to="/clientes">Clientes</RouterLink>
        <RouterLink class="block px-3 py-2 rounded hover:bg-gray-100" to="/barberos">Barberos</RouterLink>
        <RouterLink class="block px-3 py-2 rounded hover:bg-gray-100" to="/servicios">Servicios</RouterLink>
        <RouterLink class="block px-3 py-2 rounded hover:bg-gray-100" to="/pagos">Pagos</RouterLink>
        <RouterLink class="block px-3 py-2 rounded hover:bg-gray-100" to="/reportes">Reportes</RouterLink>

      </nav>
    </aside>

    <!-- Contenido -->
    <main class="flex-1 p-6">
      <slot />
    </main>
  </div>
</template>
