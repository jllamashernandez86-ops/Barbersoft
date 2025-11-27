<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\{
    ClienteController,
    BarberoController,
    ServicioController,
    CitaController,
    PagoController,
     ReporteController
        
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Recursos principales
 */
Route::apiResource('clientes',  ClienteController::class)->only(['index','store','update','destroy']);
Route::apiResource('barberos',  BarberoController::class)->only(['index','store','update','destroy']);
Route::apiResource('servicios', ServicioController::class)->only(['index','store','update','destroy']);
Route::apiResource('citas',     CitaController::class)->only(['index','store','update','destroy']);

/**
 * Disponibilidad de barbero
 * GET /api/barberos/{barbero}/disponibilidad?fecha=YYYY-MM-DD&servicio_id=ID
 */
Route::get('barberos/{barbero}/disponibilidad', [CitaController::class, 'disponibilidad']);

/**
 * Estadísticas del dashboard
 * GET /api/stats/citas?fecha=YYYY-MM-DD
 */
Route::get('stats/citas', [CitaController::class, 'stats']);

/**
 * Cambiar SOLO el estado de una cita
 * PATCH /api/citas/{cita}/estado  body: { estado: pendiente|completada|cancelada }
 */
Route::patch('citas/{cita}/estado', [CitaController::class, 'cambiarEstado']);

/**
 * Pagos
 */

Route::apiResource('pagos', PagoController::class)->only(['index','show','store','update','destroy']);


Route::get('reportes/resumen', [ReporteController::class, 'resumen']);          // dashboard de reportes
Route::get('reportes/export/csv',   [ReporteController::class, 'exportCsv']);
Route::get('reportes/export/excel', [ReporteController::class, 'exportExcel']);
Route::get('reportes/export/pdf',   [ReporteController::class, 'exportPdf']);