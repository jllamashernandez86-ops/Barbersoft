<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Barbero;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BarberoController extends Controller
{
    public function index()
    {
        // Devolver siempre ordenado y más reciente primero
        return Barbero::orderByDesc('id')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $barbero = Barbero::create($validated);
        return response()->json($barbero, 201);
    }

    public function update(Request $request, Barbero $barbero)
    {
        $validated = $request->validate([
            'nombre'   => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $barbero->update($validated);
        return response()->json($barbero);
    }

    public function destroy(Barbero $barbero)
    {
        $barbero->delete();
        return response()->json(['message' => 'Barbero eliminado']);
    }
}
