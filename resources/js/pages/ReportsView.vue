<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const month = ref(new Date().toISOString().slice(0,7)) // YYYY-MM
const loading = ref(false)
const data = ref({
  rango: { inicio: '', fin: '' },
  ingresos: { totalVentas: 0, numTransacciones: 0, promedioPorVenta: 0 },
  clientes: { activosMes: 0, citasDelMes: 0, retencion: 0 },
  rendimientoBarberos: [],   // [{ barbero, citas }]
  metodosPago: [],           // [{ metodo, pagos, total }]
})

const money = (v) =>
  new Intl.NumberFormat('es-CO',{ style:'currency', currency:'COP', maximumFractionDigits:0 })
    .format(Number(v || 0))

const fetchData = async () => {
  loading.value = true
  const { data: r } = await axios.get('/api/reportes/resumen', { params: { month: month.value } })
  data.value = r
  loading.value = false
}

// Total de pagos para porcentaje
const totalPagosMetodos = computed(() =>
  data.value.metodosPago.reduce((acc, m) => acc + (m.pagos || 0), 0)
)

// Calcula % por método
const pctMetodo = (m) => {
  const tot = totalPagosMetodos.value
  if (!tot) return 0
  return Math.round((Number(m.pagos || 0) * 100) / tot)
}

onMounted(fetchData)
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold">Reportes y Estadísticas</h1>
        <p class="text-gray-500">Resumen gerencial del período seleccionado</p>
      </div>
      <div class="flex items-center gap-2">
        <input type="month" v-model="month" class="border rounded-lg px-3 py-2" />
        <button @click="fetchData" class="px-4 py-2 rounded-lg bg-black text-white">Aplicar</button>
      </div>
    </div>

    <div v-if="loading" class="text-gray-500">Cargando…</div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <!-- Ingresos -->
      <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold mb-1">📈 Ingresos Mensuales</h3>
        <p class="text-gray-500 text-sm mb-4">
          Resumen de ingresos del {{ data.rango.inicio }} al {{ data.rango.fin }}
        </p>
        <div class="space-y-2">
          <div class="flex justify-between">
            <span>Total de Ventas:</span>
            <strong>{{ money(data.ingresos.totalVentas) }}</strong>
          </div>
          <div class="flex justify-between">
            <span>Número de Transacciones:</span>
            <strong>{{ data.ingresos.numTransacciones }}</strong>
          </div>
          <div class="flex justify-between">
            <span>Promedio por Venta:</span>
            <strong>{{ money(data.ingresos.promedioPorVenta) }}</strong>
          </div>
        </div>
      </div>

      <!-- Clientes -->
      <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold mb-1">👥 Estadísticas de Clientes</h3>
        <p class="text-gray-500 text-sm mb-4">Métricas del período</p>
        <div class="space-y-2">
          <div class="flex justify-between"><span>Clientes Activos:</span><strong>{{ data.clientes.activosMes }}</strong></div>
          <div class="flex justify-between"><span>Citas del Mes:</span><strong>{{ data.clientes.citasDelMes }}</strong></div>
          <div class="flex justify-between"><span>Tasa de Retención:</span><strong>{{ data.clientes.retencion }}%</strong></div>
        </div>
      </div>

      <!-- Rendimiento de Barberos -->
      <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold mb-1">✂️ Rendimiento de Barberos</h3>
        <p class="text-gray-500 text-sm mb-4">Estadísticas por barbero</p>

        <div class="divide-y">
          <div
            v-for="b in data.rendimientoBarberos"
            :key="b.barbero"
            class="flex items-center justify-between py-2"
          >
            <span>{{ b.barbero }}</span>
            <strong>{{ b.citas }} {{ b.citas === 1 ? 'cita' : 'citas' }}</strong>
          </div>

          <div v-if="!data.rendimientoBarberos.length" class="text-gray-500 text-sm py-2">
            Sin datos en el período.
          </div>
        </div>
      </div>

      <!-- Métodos de Pago -->
      <div class="bg-white rounded-xl border p-5">
        <div class="flex items-center justify-between mb-1">
          <h3 class="font-semibold">💳 Métodos de Pago</h3>
          <span class="text-xs text-gray-500">Total: {{ totalPagosMetodos }}</span>
        </div>
        <p class="text-gray-500 text-sm mb-4">
          Distribución en el período (Total: {{ totalPagosMetodos }})
        </p>

        <div class="space-y-2">
          <div
            v-for="m in data.metodosPago"
            :key="m.metodo"
            class="flex items-center justify-between"
          >
            <div class="flex items-center gap-3">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              <span class="capitalize">{{ m.metodo }}</span>
            </div>
            <div class="text-right">
              <strong>{{ pctMetodo(m) }}%</strong>
              <span class="text-gray-500 text-sm"> ({{ m.pagos }} pagos)</span>
            </div>
          </div>

          <div v-if="!data.metodosPago.length" class="text-gray-500 text-sm">
            Sin pagos en el período.
          </div>
        </div>
      </div>
    </div>

    <!-- Export -->
    <div class="flex items-center gap-3">
      <a :href="`/api/reportes/export/excel?month=${encodeURIComponent(month)}`" class="px-4 py-2 rounded-lg border hover:bg-gray-50">
        ⤓ Exportar Excel (.xlsx)
      </a>
      <a :href="`/api/reportes/export/pdf?month=${encodeURIComponent(month)}`" class="px-4 py-2 rounded-lg border hover:bg-gray-50">
        ⤓ Generar PDF
      </a>
    </div>
  </div>
</template>

<style scoped>
/* simple */
</style>
