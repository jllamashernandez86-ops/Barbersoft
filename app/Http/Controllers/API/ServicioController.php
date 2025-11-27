<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function index()
    {
        return Servicio::orderByDesc('id')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => ['required','string','max:100'],
            'precio'   => ['required','numeric','min:0'],
            'duracion' => ['required','integer','min:1'], // minutos
        ]);

        $servicio = Servicio::create($validated);
        return response()->json($servicio, 201);
    }

    public function update(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'nombre'   => ['required','string','max:100'],
            'precio'   => ['required','numeric','min:0'],
            'duracion' => ['required','integer','min:1'],
        ]);

        $servicio->update($validated);
        return response()->json($servicio);
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();
        return response()->json(['message' => 'Servicio eliminado']);
    }
}
