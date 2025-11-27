<script setup>
import SidebarLayout from './layouts/SidebarLayout.vue'
import auth from './auth'
</script>

<template>
  <!-- Mostrar loader mientras se verifica la sesión -->
  <div v-if="!auth.state.ready" class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="text-center">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-black"></div>
      <p class="mt-2 text-gray-600">Cargando...</p>
    </div>
  </div>

  <!-- Una vez listo, mostrar la app -->
  <router-view v-else v-slot="{ Component, route }">
    <component :is="Component" v-if="route.name === 'login'" />
    <SidebarLayout v-else>
      <component :is="Component" />
    </SidebarLayout>
  </router-view>
</template>
