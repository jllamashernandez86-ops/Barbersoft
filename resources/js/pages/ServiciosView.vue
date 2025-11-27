<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const servicios   = ref([])
const filtro      = ref('')
const cargando    = ref(false)

// modal / form
const abierto     = ref(false)
const editando    = ref(false)
const guardando   = ref(false)
const errors      = ref({})
const form        = ref({ id: null, nombre: '', precio: '', duracion: '' })

const currency = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 })

const cargar = async () => {
  cargando.value = true
  try {
    const { data } = await axios.get('/api/servicios')
    servicios.value = data
  } finally {
    cargando.value = false
  }
}

const abrirNuevo = () => {
  editando.value = false
  form.value = { id: null, nombre: '', precio: '', duracion: '' }
  errors.value = {}
  abierto.value = true
}
const abrirEditar = (s) => {
  editando.value = true
  form.value = { id: s.id, nombre: s.nombre ?? '', precio: s.precio ?? '', duracion: s.duracion ?? '' }
  errors.value = {}
  abierto.value = true
}
const cerrar = () => {
  abierto.value = false
  guardando.value = false
  errors.value = {}
}

const guardar = async () => {
  guardando.value = true
  errors.value = {}
  try {
    const payload = {
      nombre: form.value.nombre,
      precio: Number(form.value.precio),
      duracion: Number(form.value.duracion),
    }
    if (editando.value) await axios.put(`/api/servicios/${form.value.id}`, payload)
    else await axios.post('/api/servicios', payload)

    await cargar()
    cerrar()
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    } else {
      alert('Ocurrió un error. Intenta nuevamente.')
    }
  } finally {
    guardando.value = false
  }
}

const eliminarServicio = async (id) => {
  if (!confirm('¿Eliminar este servicio?')) return
  await axios.delete(`/api/servicios/${id}`)
  await cargar()
}

const filtrados = computed(() => {
  if (!filtro.value.trim()) return servicios.value
  const q = filtro.value.toLowerCase()
  return servicios.value.filter(s =>
    (s.nombre ?? '').toLowerCase().includes(q) ||
    (String(s.precio) ?? '').includes(q) ||
    (String(s.duracion) ?? '').includes(q)
  )
})

onMounted(cargar)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Servicios</h1>
      <button @click="abrirNuevo" class="bg-black text-white rounded px-4 py-2">Nuevo</button>
    </div>

    <div class="mb-4 flex gap-2">
      <input v-model="filtro" placeholder="Buscar por nombre, precio o minutos" class="border rounded px-3 py-2 w-96" />
      <button class="border rounded px-3 py-2" @click="cargar">Actualizar</button>
    </div>

    <div v-if="cargando" class="text-gray-500">Cargando...</div>

    <table v-else class="w-full bg-white border rounded overflow-hidden">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-3 border-b">Nombre</th>
          <th class="text-left p-3 border-b">Precio</th>
          <th class="text-left p-3 border-b">Duración</th>
          <th class="text-right p-3 border-b w-48">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="s in filtrados" :key="s.id" class="border-b">
          <td class="p-3">{{ s.nombre }}</td>
          <td class="p-3">{{ currency.format(s.precio) }}</td>
          <td class="p-3">{{ s.duracion }} min</td>
          <td class="p-3 text-right space-x-3">
            <button class="text-blue-600 hover:underline" @click="abrirEditar(s)">Editar</button>
            <button class="text-red-600 hover:underline" @click="eliminarServicio(s.id)">Eliminar</button>
          </td>
        </tr>
        <tr v-if="!filtrados.length">
          <td colspan="4" class="p-6 text-center text-gray-500">Sin resultados</td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <div v-if="abierto" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-semibold mb-4">{{ editando ? 'Editar servicio' : 'Nuevo servicio' }}</h2>

        <div class="space-y-4">
          <div>
            <label class="block text-sm mb-1">Nombre</label>
            <input v-model="form.nombre" class="border rounded w-full px-3 py-2" />
            <p v-if="errors.nombre" class="text-sm text-red-600 mt-1">{{ errors.nombre[0] }}</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm mb-1">Precio (COP)</label>
              <input v-model="form.precio" type="number" min="0" step="100" class="border rounded w-full px-3 py-2" />
              <p v-if="errors.precio" class="text-sm text-red-600 mt-1">{{ errors.precio[0] }}</p>
            </div>
            <div>
              <label class="block text-sm mb-1">Duración (min)</label>
              <input v-model="form.duracion" type="number" min="1" step="5" class="border rounded w-full px-3 py-2" />
              <p v-if="errors.duracion" class="text-sm text-red-600 mt-1">{{ errors.duracion[0] }}</p>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <button class="px-3 py-2 border rounded" @click="cerrar" :disabled="guardando">Cancelar</button>
          <button class="px-4 py-2 rounded bg-black text-white" @click="guardar" :disabled="guardando">
            {{ guardando ? 'Guardando...' : (editando ? 'Actualizar' : 'Guardar') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
