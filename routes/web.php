<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect('/admin/login');
});

// Rutas auxiliares para exportar reportes desde la página de Filament
Route::post('/reports/export/pagos', [ReportController::class, 'exportPagos'])->name('reports.export.pagos');
Route::post('/reports/export/resumen', [ReportController::class, 'exportResumen'])->name('reports.export.resumen');

// El resto lo gestiona Filament (/admin)

