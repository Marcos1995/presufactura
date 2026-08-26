@extends('layouts.panel')

@section('title', 'Admin — '.config('app.name'))
@section('heading', 'Admin')

@section('content')
<p class="text-muted">Solo visible para {{ config('demo.admin_email') }}. Últimos 30 días salvo que se indique otra cosa.</p>

<div class="dashboard-grid">
    <div class="stat-card stat-card--docs">
        <span class="stat-label">Usuarios</span>
        <span class="stat-value">{{ $stats['users'] }}</span>
        <span class="text-muted">+{{ $stats['new_7d'] }} esta semana · +{{ $stats['new_30d'] }} este mes</span>
    </div>
    <div class="stat-card stat-card--pending">
        <span class="stat-label">Sesiones únicas</span>
        <span class="stat-value">{{ $uniqueSessions }}</span>
        <span class="text-muted">Personas (sin bots) · 30 días</span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Visitas portada</span>
        <span class="stat-value">{{ $traffic['landing'] }}</span>
        <span class="text-muted">{{ $traffic['bot'] }} bots · {{ $traffic['http_4xx'] }} errores 4xx</span>
    </div>
    <div class="stat-card stat-card--success">
        <span class="stat-label">Documentos</span>
        <span class="stat-value">{{ $stats['quotes'] + $stats['invoices'] }}</span>
        <span class="text-muted">{{ $stats['quotes'] }} presupuestos · {{ $stats['invoices'] }} facturas</span>
    </div>
</div>

<div class="card">
    <h2 class="section-title">Visitas a la portada (14 días)</h2>
    <p class="text-muted" style="margin-top:-0.5rem">Azul = personas. Gris = bots y rastreadores.</p>
    @php
        $dailyMax = 1;
        foreach ($daily as $row) {
            $dailyMax = max($dailyMax, $row['human'] + $row['bot']);
        }
    @endphp
    <div class="admin-chart" role="img" aria-label="Visitas diarias a la portada">
        @foreach ($daily as $day => $row)
            @php $total = $row['human'] + $row['bot']; @endphp
            <div class="admin-chart__col" title="{{ $day }}: {{ $row['human'] }} personas, {{ $row['bot'] }} bots">
                <div class="admin-chart__stack" style="height: {{ max(4, (int) round(100 * $total / $dailyMax)) }}px">
                    @if ($row['bot'] > 0)
                        <span class="admin-chart__bar admin-chart__bar--bot" style="flex: {{ $row['bot'] }}"></span>
                    @endif
                    @if ($row['human'] > 0)
                        <span class="admin-chart__bar admin-chart__bar--human" style="flex: {{ $row['human'] }}"></span>
                    @endif
                </div>
                <span class="admin-chart__label">{{ \Illuminate\Support\Carbon::parse($day)->format('d/m') }}</span>
            </div>
        @endforeach
    </div>
</div>

<div class="card">
    <h2 class="section-title">Embudo (sin bots)</h2>
    @php $funnelMax = max(1, ...(array_values($funnel) ?: [0])); @endphp
    @foreach ($funnel as $name => $count)
        <div class="admin-funnel-row">
            <span class="admin-funnel-row__label">{{ $funnelLabels[$name] ?? $name }}</span>
            <span class="admin-funnel-row__track"><span class="admin-funnel-row__bar" style="width: {{ max(2, (int) round(100 * $count / $funnelMax)) }}%"></span></span>
            <span class="admin-funnel-row__n">{{ $count }}</span>
        </div>
    @endforeach
</div>

<div class="card">
    <h2 class="section-title">Tráfico y producto</h2>
    <table class="data-table">
        <tbody>
            <tr><td>Portada / público</td><td class="text-right">{{ $traffic['landing'] }}</td></tr>
            <tr><td>Zona autenticada</td><td class="text-right">{{ $traffic['authenticated'] }}</td></tr>
            <tr><td>Bots y rastreadores</td><td class="text-right">{{ $traffic['bot'] }}</td></tr>
            <tr><td>Tráfico propio</td><td class="text-right">{{ $traffic['internal'] }}</td></tr>
            <tr><td>Errores 4xx / 5xx</td><td class="text-right">{{ $traffic['http_4xx'] }} / {{ $traffic['http_5xx'] }}</td></tr>
            <tr><td>Usuarios verificados / onboarding</td><td class="text-right">{{ $stats['verified'] }} / {{ $stats['onboarded'] }}</td></tr>
            <tr><td>Clientes (todos los usuarios)</td><td class="text-right">{{ $stats['clients'] }}</td></tr>
            <tr><td>Facturas cobradas</td><td class="text-right">{{ $stats['paid_invoices'] }}</td></tr>
            <tr><td>Veri*Factu activo / con certificado</td><td class="text-right">{{ $stats['sif_enabled'] }} / {{ $stats['sif_cert'] }}</td></tr>
            <tr><td>Feedback recibido</td><td class="text-right">{{ $stats['feedback'] }}</td></tr>
        </tbody>
    </table>
    @if ($documents['quotes'] || $documents['invoices'])
        <p class="text-muted" style="margin-top:1rem">Estados: presupuestos
            @foreach ($documents['quotes'] as $st => $n) {{ $st }} {{ $n }}@if (!$loop->last), @endif @endforeach
            · facturas
            @foreach ($documents['invoices'] as $st => $n) {{ $st }} {{ $n }}@if (!$loop->last), @endif @endforeach
        </p>
    @endif
</div>

<div class="card">
    <h2 class="section-title">Usuarios ({{ $stats['users'] }})</h2>
    <div class="table-wrap">
        <table class="data-table data-table-list">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Negocio</th>
                    <th>Alta</th>
                    <th>Docs</th>
                    <th>Veri*Factu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td data-label="Nombre">{{ $u->name }}</td>
                        <td data-label="Email">{{ $u->email }}</td>
                        <td data-label="Negocio">{{ $u->business_name ?: '—' }}</td>
                        <td data-label="Alta">{{ $u->created_at?->format('d/m/Y') }}@if (!$u->email_verified_at) · sin verificar @endif</td>
                        <td data-label="Docs">{{ $u->documents_count }} · {{ $u->clients_count }} cl.</td>
                        <td data-label="Veri*Factu">
                            @if ($u->sifConfig?->enabled && $u->sifConfig->hasValidCertificate())
                                Fiscal
                            @elseif ($u->sifConfig?->enabled)
                                Sin certificado
                            @else
                                Proforma
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Aún no hay usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
