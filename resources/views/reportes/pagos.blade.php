<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte de Pagos</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1 { font-size: 18px; margin: 0 0 6px 0; }
    p { margin: 0 0 10px 0; color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 6px; }
    th { background: #f5f5f5; text-align: left; }
    .right { text-align: right; }
    .muted { color: #777; }
  </style>
</head>
<body>
  <h1>Reporte de Pagos</h1>
  <p class="muted">Período: {{ $inicio }} a {{ $fin }}</p>

  <table>
    <thead>
      <tr>
        <th>Fecha Pago</th>
        <th>Cliente</th>
        <th>Barbero</th>
        <th>Servicio</th>
        <th>Método</th>
        <th class="right">Monto</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($rows as $r)
        <tr>
          <td>{{ $r->fecha_pago }}</td>
          <td>{{ $r->cliente }}</td>
          <td>{{ $r->barbero ?? '—' }}</td>
          <td>{{ $r->servicio }}</td>
          <td>{{ $r->metodo }}</td>
          <td class="right">{{ number_format((float)$r->monto, 0, ',', '.') }}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="muted">Sin registros en el período.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
