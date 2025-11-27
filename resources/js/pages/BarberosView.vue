<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const cargando     = ref(false)
const barberos     = ref([])
const filtro       = ref('')

// Modal & formulario
const abierto      = ref(false)
const editando     = ref(false)
const guardando    = ref(false)
const form         = ref({ id: null, nombre: '', telefono: '' })
const errors       = ref({})

// --- Lógica
const cargar = async () => {
  cargando.value = true
  try {
    const { data } = await axios.get('/api/barberos')
    barberos.value = data
  } finally {
    cargando.value = false
  }
}

const abrirNuevo = () => {
  editando.value = false
  form.value = { id: null, nombre: '', telefono: '' }
  errors.value = {}
  abierto.value = true
}

const abrirEditar = (b) => {
  editando.value = true
  form.value = { id: b.id, nombre: b.nombre ?? '', telefono: b.telefono ?? '' }
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
    if (editando.value) {
      await axios.put(`/api/barberos/${form.value.id}`, {
        nombre: form.value.nombre,
        telefono: form.value.telefono,
      })
    } else {
      await axios.post('/api/barberos', {
        nombre: form.value.nombre,
        telefono: form.value.telefono,
      })
    }
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

const eliminarBarbero = async (id) => {
  if (!confirm('¿Eliminar este barbero?')) return
  await axios.delete(`/api/barberos/${id}`)
  await cargar()
}

const filtrados = computed(() => {
  if (!filtro.value.trim()) return barberos.value
  const q = filtro.value.toLowerCase()
  return barberos.value.filter(b =>
    (b.nombre ?? '').toLowerCase().includes(q) ||
    (b.telefono ?? '').toLowerCase().includes(q)
  )
})

onMounted(cargar)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Barberos</h1>
      <button @click="abrirNuevo" class="bg-black text-white rounded px-4 py-2">
        Nuevo
      </button>
    </div>

    <div class="mb-4 flex gap-2">
      <input
        v-model="filtro"
        type="text"
        placeholder="Buscar por nombre o teléfono"
        class="border rounded px-3 py-2 w-80"
      />
      <button @click="cargar" class="border rounded px-3 py-2">Actualizar</button>
    </div>

    <div v-if="cargando" class="text-gray-500">Cargando...</div>

    <table v-else class="w-full bg-white border rounded overflow-hidden">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-3 border-b">Nombre</th>
          <th class="text-left p-3 border-b">Teléfono</th>
          <th class="text-right p-3 border-b w-48">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="b in filtrados" :key="b.id" class="border-b">
          <td class="p-3">{{ b.nombre }}</td>
          <td class="p-3">{{ b.telefono || '—' }}</td>
          <td class="p-3 text-right space-x-3">
            <button class="text-blue-600 hover:underline" @click="abrirEditar(b)">Editar</button>
            <button class="text-red-600 hover:underline" @click="eliminarBarbero(b.id)">Eliminar</button>
          </td>
        </tr>
        <tr v-if="!filtrados.length">
          <td colspan="3" class="p-6 text-center text-gray-500">Sin resultados</td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <div v-if="abierto" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-semibold mb-4">
          {{ editando ? 'Editar barbero' : 'Nuevo barbero' }}
        </h2>

        <div class="space-y-4">
          <div>
            <label class="block text-sm mb-1">Nombre</label>
            <input v-model="form.nombre" class="border rounded w-full px-3 py-2" />
            <p v-if="errors.nombre" class="text-sm text-red-600 mt-1">{{ errors.nombre[0] }}</p>
          </div>
          <div>
            <label class="block text-sm mb-1">Teléfono</label>
            <input v-model="form.telefono" class="border rounded w-full px-3 py-2" />
            <p v-if="errors.telefono" class="text-sm text-red-600 mt-1">{{ errors.telefono[0] }}</p>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <button class="px-3 py-2 border rounded" @click="cerrar" :disabled="guardando">Cancelar</button>
          <button
            class="px-4 py-2 rounded bg-black text-white"
            @click="guardar"
            :disabled="guardando"
          >
            {{ guardando ? 'Guardando...' : (editando ? 'Actualizar' : 'Guardar') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
