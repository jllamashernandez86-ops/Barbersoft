<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const api = axios.create({ baseURL: '/api' })

const fecha = ref(new Date().toISOString().slice(0,10))
const estado = ref('pagado')
const pagos = ref([])
const cargando = ref(false)
const creando = ref(false)
const showModal = ref(false)

const form = ref({
  cita_id: '',
  metodo: 'efectivo',
  monto: '',
  estado: 'pagado',
  pagado_at: new Date().toISOString().slice(0,10),
})

const citasDelDia = ref([]) // para seleccionar cita al crear pago

function formatCOP(n){
  if(n==null) return ''
  return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(n)
}

async function loadPagos() {
  cargando.value = true
  try {
    const { data } = await api.get('/pagos', { params: { fecha: fecha.value, estado: estado.value || undefined }})
    pagos.value = data
  } finally {
    cargando.value = false
  }
}

async function loadCitas() {
  const { data } = await api.get('/citas', { params: { fecha: fecha.value }})
  citasDelDia.value = data
}

async function filtrar() {
  await Promise.all([loadPagos(), loadCitas()])
}

async function crearPago() {
  creando.value = true
  try {
    const payload = { ...form.value }
    if (!payload.monto) delete payload.monto // que el backend tome precio del servicio
    const { data } = await api.post('/pagos', payload)
    showModal.value = false
    await loadPagos()
    // reset
    form.value = { cita_id:'', metodo:'efectivo', monto:'', estado:'pagado', pagado_at:new Date().toISOString().slice(0,10) }
  } catch (e) {
    alert(e?.response?.data?.message || 'No se pudo crear el pago')
  } finally {
    creando.value = false
  }
}

async function eliminarPago(p) {
  if (!confirm('¿Eliminar pago?')) return
  await api.delete(`/pagos/${p.id}`)
  await loadPagos()
}

onMounted(filtrar)
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Pagos</h1>

    <div class="flex items-end gap-3 mb-4">
      <div class="flex flex-col text-sm">
        <label class="mb-1">Fecha</label>
        <input type="date" v-model="fecha" class="border rounded p-2" />
      </div>
      <div class="flex flex-col text-sm">
        <label class="mb-1">Estado</label>
        <select v-model="estado" class="border rounded p-2">
          <option value="">Todos</option>
          <option value="pagado">pagado</option>
          <option value="pendiente">pendiente</option>
          <option value="anulado">anulado</option>
        </select>
      </div>
      <button class="px-4 py-2 rounded bg-black text-white" @click="filtrar">Filtrar</button>

      <div class="flex-1"></div>

      <button class="px-4 py-2 rounded bg-black text-white" @click="() => { showModal = true; loadCitas(); }">
        Nuevo pago
      </button>
    </div>

    <div v-if="cargando" class="text-sm opacity-70 mb-2">Cargando…</div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-2 border">ID</th>
            <th class="p-2 border">Cita</th>
            <th class="p-2 border">Cliente</th>
            <th class="p-2 border">Servicio</th>
            <th class="p-2 border">Método</th>
            <th class="p-2 border">Monto</th>
            <th class="p-2 border">Estado</th>
            <th class="p-2 border">Fecha</th>
            <th class="p-2 border"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in pagos" :key="p.id">
            <td class="p-2 border text-center">{{ p.id }}</td>
            <td class="p-2 border">#{{ p.cita_id }}</td>
            <td class="p-2 border">{{ p.cita?.cliente?.nombre }}</td>
            <td class="p-2 border">{{ p.cita?.servicio?.nombre }}</td>
            <td class="p-2 border">{{ p.metodo }}</td>
            <td class="p-2 border">{{ formatCOP(p.monto) }}</td>
            <td class="p-2 border capitalize">{{ p.estado }}</td>
            <td class="p-2 border">{{ p.pagado_at ?? (p.created_at?.slice(0,10)) }}</td>
            <td class="p-2 border text-right">
              <button class="text-red-600" @click="eliminarPago(p)">Eliminar</button>
            </td>
          </tr>
          <tr v-if="!pagos.length">
            <td colspan="9" class="p-3 text-center text-gray-500">Sin pagos para los filtros seleccionados</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal simple -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center">
      <div class="bg-white rounded p-4 w-[480px]">
        <h2 class="text-lg font-semibold mb-3">Nuevo pago</h2>

        <div class="grid grid-cols-2 gap-3">
          <label class="text-sm flex flex-col">
            Cita
            <select v-model="form.cita_id" class="border rounded p-2">
              <option disabled value="">Seleccione…</option>
              <option v-for="c in citasDelDia" :key="c.id" :value="c.id">
                #{{ c.id }} — {{ c.cliente?.nombre }} — {{ c.servicio?.nombre }} ({{ c.hora?.slice(0,5) }})
              </option>
            </select>
          </label>

          <label class="text-sm flex flex-col">
            Método
            <select v-model="form.metodo" class="border rounded p-2">
              <option>efectivo</option>
              <option>nequi</option>
              <option>daviplata</option>
              <option>transferencia</option>
              <option>tarjeta</option>
            </select>
          </label>

          <label class="text-sm flex flex-col">
            Monto (opcional)
            <input type="number" min="0" step="1000" v-model="form.monto" class="border rounded p-2" placeholder="Auto desde servicio" />
          </label>

          <label class="text-sm flex flex-col">
            Estado
            <select v-model="form.estado" class="border rounded p-2">
              <option>pagado</option>
              <option>pendiente</option>
              <option>anulado</option>
            </select>
          </label>

          <label class="text-sm flex flex-col col-span-2">
            Fecha pago
            <input type="date" v-model="form.pagado_at" class="border rounded p-2" />
          </label>
        </div>

        <div class="flex justify-end gap-2 mt-4">
          <button class="px-3 py-2" @click="showModal=false">Cancelar</button>
          <button class="px-4 py-2 rounded bg-black text-white" :disabled="!form.cita_id || creando" @click="crearPago">
            Guardar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
table th, table td { white-space: nowrap; }
</style>
