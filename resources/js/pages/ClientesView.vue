<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
const api = axios.create({ baseURL: '/api' });

const lista = ref([]);
const modal = ref(false);
const editando = ref(null);
const form = ref({ nombre:'', telefono:'', correo:'' });
const msg = ref('');

async function cargar(){ lista.value = (await api.get('/clientes')).data; }
function abrirNuevo(){ editando.value=null; form.value={nombre:'',telefono:'',correo:''}; modal.value=true; }
function abrirEditar(c){ editando.value=c.id; form.value={ nombre:c.nombre, telefono:c.telefono, correo:c.correo }; modal.value=true; }

async function guardar(){
  try{
    if(editando.value){ await api.put(`/clientes/${editando.value}`, form.value); }
    else { await api.post('/clientes', form.value); }
    modal.value=false; await cargar(); msg.value='✅ Guardado';
  }catch(e){ msg.value = e?.response?.data?.message ?? 'Error'; }
}

async function eliminar(id){
  if(!confirm('¿Eliminar?')) return;
  await api.delete(`/clientes/${id}`); await cargar();
}

onMounted(cargar);
</script>

<template>
  <div class="max-w-4xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Clientes</h1>
      <button @click="abrirNuevo" class="px-3 py-2 bg-black text-white rounded">Nuevo</button>
    </div>
    <p class="text-sm h-5" :class="{'text-green-600': msg.startsWith('✅')}">{{ msg }}</p>

    <table class="w-full bg-white border rounded">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-2 border text-left">Nombre</th>
          <th class="p-2 border text-left">Teléfono</th>
          <th class="p-2 border text-left">Correo</th>
          <th class="p-2 border w-36"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="c in lista" :key="c.id">
          <td class="p-2 border">{{ c.nombre }}</td>
          <td class="p-2 border">{{ c.telefono }}</td>
          <td class="p-2 border">{{ c.correo }}</td>
          <td class="p-2 border">
            <button class="underline mr-2" @click="abrirEditar(c)">Editar</button>
            <button class="underline text-red-600" @click="eliminar(c.id)">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal simple -->
    <div v-if="modal" class="fixed inset-0 bg-black/40 flex items-center justify-center">
      <div class="bg-white rounded p-4 w-full max-w-md">
        <h2 class="text-lg font-semibold mb-3">{{ editando ? 'Editar' : 'Nuevo' }} cliente</h2>
        <div class="space-y-3">
          <input v-model="form.nombre" class="w-full border rounded p-2" placeholder="Nombre" />
          <input v-model="form.telefono" class="w-full border rounded p-2" placeholder="Teléfono" />
          <input v-model="form.correo" class="w-full border rounded p-2" placeholder="Correo" />
        </div>
        <div class="mt-4 flex justify-end gap-2">
          <button @click="modal=false" class="px-3 py-2 border rounded">Cancelar</button>
          <button @click="guardar" class="px-3 py-2 bg-black text-white rounded">Guardar</button>
        </div>
      </div>
    </div>
  </div>
</template>

