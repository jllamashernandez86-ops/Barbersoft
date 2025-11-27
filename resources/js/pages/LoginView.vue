<script setup>
import { ref } from 'vue'
import auth from '../auth'
import { useRouter } from 'vue-router'

const email = ref('')
const password = ref('')
const remember = ref(true)
const loading = ref(false)
const error = ref('')

const router = useRouter()

const submit = async () => {
  loading.value = true
  error.value = ''
  const ok = await auth.login(email.value, password.value, remember.value)
  loading.value = false
  if (ok) {
    router.push('/')
  } else {
    error.value = auth.state.error || 'No se pudo iniciar sesión'
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="w-full max-w-md bg-white border rounded-xl p-8 shadow-sm">
      <h1 class="text-2xl font-semibold mb-1">Iniciar sesión</h1>
      <p class="text-gray-500 mb-6">Accede con tus credenciales</p>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Correo</label>
          <input v-model="email" type="email" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black/60" required />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Contraseña</label>
          <input v-model="password" type="password" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black/60" required />
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
          <input v-model="remember" type="checkbox" class="rounded border-gray-300" /> Recordarme
        </label>

        <button :disabled="loading" class="w-full bg-black text-white rounded-lg py-2.5 hover:bg-black/90 disabled:opacity-60">
          {{ loading ? 'Ingresando…' : 'Entrar' }}
        </button>
      </form>

      <p v-if="error" class="text-red-600 text-sm mt-4">{{ error }}</p>
    </div>
  </div>
</template>
