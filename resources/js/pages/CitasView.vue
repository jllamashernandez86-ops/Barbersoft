<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

const api = axios.create({ baseURL: '/api' })

const clientes  = ref([])
const barberos  = ref([])
const servicios = ref([])

const fecha = ref(new Date().toISOString().slice(0, 10))
const selectedCliente  = ref(null)
const selectedBarbero  = ref(null)
const selectedServicio = ref(null)

const hora  = ref('')
const slots = ref([])
const cargandoSlots = ref(false)
const errorSlots = ref('')

const citas = ref([])
const loading = ref(false)
const msg = ref('')

// Estados: value (BD) y label (UI)
const estados = [
  { value: 'pendiente',  label: 'Pendiente'  },
  { value: 'completada', label: 'Completada' },
  { value: 'cancelada',  label: 'Cancelada'  },
]

async function loadBase () {
  try {
    const [c1,b1,s1] = await Promise.all([
      api.get('/clientes'),
      api.get('/barberos'),
      api.get('/servicios')
    ])
    clientes.value  = c1.data
    barberos.value  = b1.data
    servicios.value = s1.data
  } finally {
    await loadCitas()
    await loadDisponibilidad()
  }
}

async function loadCitas () {
  loading.value = true
  try {
    const r = await api.get('/citas', { params: { fecha: fecha.value } })
    citas.value = r.data?.data ?? r.data
  } catch (e) {
    console.error('Error cargando citas', e)
    citas.value = []
  } finally {
    loading.value = false
  }
}

async function loadDisponibilidad () {
  // limpiar estado de UI
  slots.value = []
  errorSlots.value = ''
  // no llames al back si falta alguno
  if (!selectedBarbero.value || !selectedServicio.value || !fecha.value) {
    hora.value = ''
    return
  }

  cargandoSlots.value = true
  try {
    const { data } = await api.get(`/barberos/${selectedBarbero.value}/disponibilidad`, {
      params: { fecha: fecha.value, servicio_id: selectedServicio.value } // <-- clave
    })
    const horas = Array.isArray(data) ? data : (data.horas ?? data.disponibles ?? [])
    slots.value = Array.isArray(horas) ? horas : []
    if (!slots.value.length) errorSlots.value = 'No hay horas libres para esa combinación.'
  } catch (e) {
    console.error('Disponibilidad error', e)
    errorSlots.value = e?.response?.data?.message || 'No se pudo cargar la disponibilidad'
  } finally {
    cargandoSlots.value = false
    // si la hora actual ya no existe, límpiala
    if (hora.value && !slots.value.includes(hora.value)) {
      hora.value = ''
    }
  }
}

async function crearCita () {
  msg.value = ''
  try {
    await api.post('/citas', {
      cliente_id:  selectedCliente.value,
      barbero_id:  selectedBarbero.value,
      servicio_id: selectedServicio.value,
      fecha:       fecha.value,
      hora:        hora.value,
      estado: 'pendiente',
    })
    await Promise.all([loadCitas(), loadDisponibilidad()])
    msg.value = '✅ Cita creada'
  } catch (e) {
    console.error(e)
    msg.value = e?.response?.data?.message ?? 'Error creando la cita'
  }
}

async function eliminar (id) {
  await api.delete(`/citas/${id}`)
  await Promise.all([loadCitas(), loadDisponibilidad()])
}

const cambiarEstado = async (cita) => {
  const estadoAnterior = cita.estado
  try {
    await api.patch(`/citas/${cita.id}/estado`, { estado: cita.estado })
  } catch (e) {
    console.error(e)
    cita.estado = estadoAnterior
    alert(e?.response?.data?.message ?? 'No se pudo actualizar el estado')
  }
}

// Recalcular disponibilidad cuando cambie cualquiera de los 3
watch([selectedBarbero, selectedServicio, fecha], () => {
  hora.value = ''
  loadDisponibilidad()
})

// Refrescar citas cuando cambie la fecha
watch(fecha, loadCitas)

onMounted(loadBase)
</script>

<template>
  <div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Citas</h1>

    <div class="grid md:grid-cols-5 gap-3 items-end">
      <div>
        <label class="block text-sm mb-1">Cliente</label>
        <select v-model="selectedCliente" class="w-full border rounded p-2">
          <option :value="null" disabled>Selecciona…</option>
          <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm mb-1">Barbero</label>
        <select v-model="selectedBarbero" class="w-full border rounded p-2">
          <option :value="null" disabled>Selecciona…</option>
          <option v-for="b in barberos" :key="b.id" :value="b.id">{{ b.nombre }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm mb-1">Servicio</label>
        <select v-model="selectedServicio" class="w-full border rounded p-2">
          <option :value="null" disabled>Selecciona…</option>
          <option v-for="s in servicios" :key="s.id" :value="s.id">
            {{ s.nombre }} — ${{ Number(s.precio ?? 0).toLocaleString() }} ({{ s.duracion }} min)
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm mb-1">Fecha</label>
        <input type="date" v-model="fecha" class="w-full border rounded p-2" />
      </div>

      <div>
        <label class="block text-sm mb-1">Hora</label>
        <select v-model="hora" class="w-full border rounded p-2">
          <option :value="''" disabled>Selecciona…</option>
          <option v-for="h in slots" :key="`slot-${h}`" :value="h">{{ h }}</option>
        </select>
        <div v-if="cargandoSlots" class="text-xs opacity-70 mt-1">Cargando horas…</div>
        <div v-else-if="errorSlots" class="text-xs text-red-600 mt-1">{{ errorSlots }}</div>
      </div>
    </div>

    <div class="mt-3 flex gap-2 items-center">
      <button
        @click="crearCita"
        :disabled="!selectedCliente || !selectedBarbero || !selectedServicio || !fecha || !hora"
        class="px-4 py-2 rounded bg-black text-white disabled:opacity-50"
      >
        Crear cita
      </button>
      <span
        class="text-sm"
        :class="{
          'text-green-600': msg.startsWith('✅'),
          'text-red-600': msg.startsWith('Error')
        }"
      >
        {{ msg }}
      </span>
    </div>

    <hr class="my-6" />

    <div class="flex items-center gap-3 mb-3">
      <h2 class="text-xl font-semibold">Citas del {{ fecha }}</h2>
      <button @click="loadCitas" class="text-sm underline">Actualizar</button>
    </div>

    <div v-if="loading">Cargando…</div>

    <table v-else class="w-full border rounded bg-white">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-2 border">Hora</th>
          <th class="text-left p-2 border">Cliente</th>
          <th class="text-left p-2 border">Barbero</th>
          <th class="text-left p-2 border">Servicio</th>
          <th class="text-left p-2 border">Estado</th>
          <th class="p-2 border"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="c in citas" :key="c.id">
          <td class="p-2 border">{{ (c.hora ?? '').slice(0, 5) }}</td>
          <td class="p-2 border">{{ c.cliente?.nombre }}</td>
          <td class="p-2 border">{{ c.barbero?.nombre }}</td>
          <td class="p-2 border">{{ c.servicio?.nombre }}</td>

          <td class="p-2 border">
            <select
              v-model="c.estado"
              @change="cambiarEstado(c)"
              class="border rounded p-1 capitalize"
            >
              <option v-for="e in estados" :key="e.value" :value="e.value">
                {{ e.label }}
              </option>
            </select>
          </td>

          <td class="p-2 border">
            <button @click="eliminar(c.id)" class="text-red-600 underline">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
