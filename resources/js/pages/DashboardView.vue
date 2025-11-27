<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// ----- state
const loading = ref(true)
const todayISO = new Date().toISOString().slice(0,10)

const stats = ref({
  total: 0,
  pendientes: 0,
  completadas: 0,
  canceladas: 0,
})
const clientesActivos = ref(0)
const ingresosHoy = ref(0)
const citasHoy = ref([])           // [{hora, cliente, servicio, barbero, estado}]
const topServicios = ref([])       // [{nombre, conteo, precio}]

// ----- helpers
const money = (v) =>
  new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0})
    .format(Number(v||0))

const proximaCita = computed(() => {
  if (!citasHoy.value.length) return null
  const sorted = [...citasHoy.value].sort((a,b) => (a.hora || '').localeCompare(b.hora || ''))
  return sorted[0]
})

// ----- fetchers
const fetchStats = async () => {
  try {
    const { data } = await axios.get('/api/stats/citas', { params: { fecha: todayISO } })
    stats.value = {
      total: data?.total ?? 0,
      pendientes: data?.pendientes ?? 0,
      completadas: data?.completadas ?? 0,
      canceladas: data?.canceladas ?? 0,
    }
    // 👇 ingresos desde backend (admite snake o camel)
    ingresosHoy.value = data?.ingresos_hoy ?? data?.ingresosHoy ?? 0
  } catch (e) {
    console.error('Error cargando stats:', e)
  }
}

const fetchCitasHoy = async () => {
  const { data } = await axios.get('/api/citas', { params: { fecha: todayISO } })
  // normalizamos (asumiendo relaciones cliente/barbero/servicio)
  citasHoy.value = (data || []).map(c => ({
    id: c.id,
    hora: (c.hora ?? '').slice(0,5),
    estado: (c.estado || '').toLowerCase() || 'pendiente',
    cliente: c.cliente?.nombre || c.cliente?.name || '—',
    barbero: c.barbero?.nombre || '—',
    servicio: c.servicio?.nombre || '—',
    precio: Number(c.servicio?.precio || 0),
  }))
}

const fetchClientes = async () => {
  try {
    const { data } = await axios.get('/api/clientes')
    clientesActivos.value = Array.isArray(data) ? data.length : 0
  } catch {
    clientesActivos.value = 0
  }
}

const fetchServiciosPopulares = async () => {
  try {
    const [{ data: servicios }] = await Promise.all([
      axios.get('/api/servicios')
    ])
    // ranking por conteo en las citas de HOY (sincrónico con citasHoy)
    const conteo = {}
    citasHoy.value.forEach(c => {
      if (!conteo[c.servicio]) conteo[c.servicio] = 0
      conteo[c.servicio]++
    })
    const arr = Object.entries(conteo).map(([nombre, cnt]) => {
      const s = servicios.find(x => x.nombre === nombre)
      return { nombre, conteo: cnt, precio: Number(s?.precio || 0) }
    })
    topServicios.value = arr.sort((a,b) => b.conteo - a.conteo).slice(0, 5)
  } catch (e) {
    console.error('Error servicios populares:', e)
    topServicios.value = []
  }
}

const loadAll = async () => {
  loading.value = true
  await fetchCitasHoy()
  await Promise.all([
    fetchStats(),              // 🔥 ingresosHoy viene de aquí
    fetchClientes(),
    fetchServiciosPopulares(),
  ])
  loading.value = false
}

onMounted(loadAll)
</script>

<template>
  <div class="space-y-6">
    <!-- header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold">BarberSoft Dashboard</h1>
        <p class="text-gray-500">Gestión integral de tu barbería</p>
      </div>
      <div class="hidden md:flex items-center gap-2">
        <input
          placeholder="Buscar clientes, citas, servicios…"
          class="w-80 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-black/60"
        />
      </div>
    </div>

    <!-- metric cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white rounded-xl border p-5">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Citas Hoy</p>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-black/5">📅</span>
        </div>
        <p class="mt-2 text-3xl font-semibold">{{ stats.total }}</p>
        <p class="text-xs text-gray-400 mt-1">+{{ Math.max(0, stats.total - 10) }} vs ayer</p>
      </div>

      <div class="bg-white rounded-xl border p-5">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Clientes Activos</p>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-black/5">👥</span>
        </div>
        <p class="mt-2 text-3xl font-semibold">{{ clientesActivos }}</p>
        <p class="text-xs text-gray-400 mt-1">+8 esta semana</p>
      </div>

      <div class="bg-white rounded-xl border p-5">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Ingresos Hoy</p>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-black/5">💲</span>
        </div>
        <p class="mt-2 text-3xl font-semibold">{{ money(ingresosHoy) }}</p>
        <p class="text-xs text-gray-400 mt-1">+15% vs ayer</p>
      </div>

      <div class="bg-white rounded-xl border p-5">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500">Servicios Completados</p>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-black/5">✂️</span>
        </div>
        <p class="mt-2 text-3xl font-semibold">{{ stats.completadas }}</p>
        <p class="text-xs text-gray-400 mt-1">+3 vs ayer</p>
      </div>
    </div>

    <!-- contenido principal -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <!-- Citas de hoy -->
      <div class="bg-white rounded-xl border p-5">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <span class="text-lg font-semibold">Citas de Hoy</span>
          </div>
          <button
            class="text-sm border px-3 py-1.5 rounded-lg hover:bg-gray-50"
            @click="loadAll"
          >
            Actualizar
          </button>
        </div>

        <div v-if="loading" class="text-gray-500">Cargando…</div>
        <div v-else class="space-y-3 max-h-[420px] overflow-auto pr-1">
          <div
            v-for="c in [...citasHoy].sort((a,b) => (a.hora || '').localeCompare(b.hora || ''))"
            :key="c.id"
            class="rounded-lg border p-3 flex items-center justify-between"
          >
            <div>
              <p class="font-medium">{{ c.cliente }}</p>
              <p class="text-sm text-gray-500">
                {{ c.servicio }}
                <span class="text-gray-400"> · </span>
                Barbero: {{ c.barbero }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-sm font-semibold">{{ c.hora }}</p>
              <span
                class="inline-flex px-2 py-0.5 rounded-full text-xs capitalize"
                :class="{
                  'bg-black text-white': c.estado === 'completada',
                  'bg-yellow-100 text-yellow-800': c.estado === 'pendiente',
                  'bg-red-100 text-red-700': c.estado === 'cancelada',
                }"
              >
                {{ c.estado }}
              </span>
            </div>
          </div>

          <div v-if="!citasHoy.length" class="text-gray-500 text-sm">
            No hay citas para hoy.
          </div>
        </div>
      </div>

      <!-- Servicios populares -->
      <div class="bg-white rounded-xl border p-5">
        <div class="flex items-center justify-between mb-3">
          <span class="text-lg font-semibold">Servicios Populares</span>
        </div>

        <div v-if="loading" class="text-gray-500">Cargando…</div>
        <div v-else class="space-y-2">
          <div
            v-for="s in topServicios"
            :key="s.nombre"
            class="rounded-lg border p-3 flex items-center justify-between"
          >
            <div>
              <p class="font-medium">{{ s.nombre }}</p>
              <p class="text-sm text-gray-500">{{ s.conteo }} servicios</p>
            </div>
            <p class="text-emerald-600 font-semibold">
              {{ money(s.precio) }}
            </p>
          </div>

          <div v-if="!topServicios.length" class="text-gray-500 text-sm">
            Aún no hay ranking para hoy.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* scroll sutil para la lista de citas */
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 8px; }
</style>
