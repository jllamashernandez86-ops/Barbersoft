<!-- resources/views/reportes/resumen.blade.php -->
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte resumen</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#222; }
    h1 { font-size: 18px; margin: 0 0 12px 0; }
    h3 { font-size: 14px; margin: 18px 0 8px; }
    table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    th, td { border: 1px solid #ddd; padding: 6px; }
    th { background: #f3f4f6; text-align: left; }
    .right { text-align: right; }
    .mt { margin-top: 10px; }
  </style>
</head>
<body>
@php
  $s = $summary;
  $money = fn($v) => '$ '.number_format((float)$v, 0, ',', '.');
@endphp

<h1>Reporte: {{ $s['rango']['inicio'] }} al {{ $s['rango']['fin'] }}</h1>

<h3>Ingresos del período</h3>
<table>
  <thead>
    <tr>
      <th>Total</th>
      <th>Transacciones</th>
      <th>Promedio</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="right">{{ $money($s['ingresos']['total']) }}</td>
      <td class="right">{{ $s['ingresos']['transacciones'] }}</td>
      <td class="right">{{ $money($s['ingresos']['promedio']) }}</td>
    </tr>
  </tbody>
</table>

<h3>Estadísticas de clientes</h3>
<table>
  <thead>
    <tr>
      <th>Activos</th>
      <th>Citas del período</th>
      <th>Retención (%)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="right">{{ $s['clientes']['activos'] }}</td>
      <td class="right">{{ $s['clientes']['citas'] }}</td>
      <td class="right">{{ $s['clientes']['retencion'] }}</td>
    </tr>
  </tbody>
</table>

<h3>Métodos de pago</h3>
<table>
  <thead>
    <tr>
      <th>Método</th>
      <th>Pagos</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($s['metodos'] as $m)
      <tr>
        <td>{{ $m['metodo'] }}</td>
        <td class="right">{{ $m['pagos'] }}</td>
        <td class="right">{{ $money($m['total']) }}</td>
      </tr>
    @empty
      <tr><td colspan="3">Sin pagos.</td></tr>
    @endforelse
  </tbody>
</table>

<h3>Rendimiento por barbero</h3>
<table>
  <thead>
    <tr>
      <th>Barbero</th>
      <th>Citas</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($s['barberos'] as $b)
      <tr>
        <td>{{ $b['barbero'] }}</td>
        <td class="right">{{ $b['citas'] }}</td>
      </tr>
    @empty
      <tr><td colspan="2">Sin datos.</td></tr>
    @endforelse
  </tbody>
</table>

</body>
</html>
