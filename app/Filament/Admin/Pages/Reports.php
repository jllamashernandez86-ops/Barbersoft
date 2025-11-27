<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Forms; 
use Filament\Tables; 
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\ReportePagosExport;
use App\Exports\ReporteResumenExport;
use Maatwebsite\Excel\Facades\Excel;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.reports';
    protected static ?string $navigationLabel = 'Reportes';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function getHeading(): string
    {
        return 'Reportes';
    }
}
