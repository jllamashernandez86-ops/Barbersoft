import { reactive, computed } from 'vue'
import axios from 'axios'

axios.defaults.withCredentials = true

const state = reactive({
  user: null,
  ready: false,
  error: null,
})

async function init() {
  try {
    const { data } = await axios.get('/user')
    state.user = data || null
  } catch (e) {
    state.user = null
  } finally {
    state.ready = true
  }
}

async function login(email, password, remember = false) {
  state.error = null
  // Asegurar cookie CSRF (Sanctum)
  await axios.get('/sanctum/csrf-cookie')
  try {
    await axios.post('/login', { email, password, remember })
    const { data } = await axios.get('/user')
    state.user = data
    return true
  } catch (e) {
    state.error = e?.response?.data?.message || 'Credenciales inválidas'
    return false
  }
}

async function logout() {
  await axios.post('/logout')
  state.user = null
}

export default {
  state,
  init,
  login,
  logout,
  isAuthenticated: computed(() => !!state.user),
}
