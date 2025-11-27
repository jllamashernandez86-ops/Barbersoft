<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cliente;

class Clientes extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    public $modalOpen = false;
    public $confirmingDelete = false;

    public $editingId = null;
    public $nombre = '';
    public $telefono = '';
    public $correo = '';
    public $deleteId = null;

    protected $rules = [
        'nombre'   => 'required|string|max:100',
        'telefono' => 'nullable|string|max:20',
        'correo'   => 'nullable|email|max:100',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'correo.email'    => 'Debes ingresar un correo válido.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $c = Cliente::findOrFail($id);
            $this->nombre   = $c->nombre;
            $this->telefono = $c->telefono;
            $this->correo   = $c->correo;
        } else {
            $this->nombre = $this->telefono = $this->correo = '';
        }

        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $c = Cliente::findOrFail($this->editingId);
            $c->update([
                'nombre'   => $this->nombre,
                'telefono' => $this->telefono,
                'correo'   => $this->correo,
            ]);
            session()->flash('ok', 'Cliente actualizado.');
        } else {
            Cliente::create([
                'nombre'   => $this->nombre,
                'telefono' => $this->telefono,
                'correo'   => $this->correo,
            ]);
            session()->flash('ok', 'Cliente creado.');
        }

        $this->modalOpen = false;
        $this->reset(['editingId', 'nombre', 'telefono', 'correo']);
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        if ($this->deleteId) {
            Cliente::where('id', $this->deleteId)->delete();
            session()->flash('ok', 'Cliente eliminado.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $q = Cliente::query()
            ->when($this->search, function ($qq) {
                $s = '%'.$this->search.'%';
                $qq->where('nombre', 'like', $s)
                   ->orWhere('telefono', 'like', $s)
                   ->orWhere('correo', 'like', $s);
            })
            ->orderBy('nombre');

        return view('livewire.clientes', [
            'items' => $q->paginate($this->perPage),
        ])->layout('layouts.app');
    }
}
