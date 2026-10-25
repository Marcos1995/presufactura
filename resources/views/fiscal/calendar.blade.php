@extends('layouts.panel')

@section('title', 'Calendario fiscal — '.config('app.name'))
@section('heading', 'Calendario fiscal')

@section('content')
<p class="alert alert-warning">Orientativo. No presenta modelos ni sustituye a un asesor. Las estimaciones salen solo de tus facturas en PresuFactura.</p>

<form method="POST" action="{{ route('fiscal.calendar.store') }}" class="stack-form" style="max-width:32rem;margin-bottom:2rem">
    @csrf
    <h2>Tu perfil (5 preguntas)</h2>
    <label for="fiscal_activity">Actividad</label>
    <input id="fiscal_activity" name="fiscal_activity" value="{{ old('fiscal_activity', $company->fiscal_activity) }}" required maxlength="120">

    <label for="vat_regime">Régimen de IVA</label>
    <select id="vat_regime" name="vat_regime" required>
        <option value="general" @selected(old('vat_regime', $company->vat_regime) === 'general')>General</option>
        <option value="recargo" @selected(old('vat_regime', $company->vat_regime) === 'recargo')>Recargo de equivalencia</option>
        <option value="exento" @selected(old('vat_regime', $company->vat_regime) === 'exento')>Exento</option>
    </select>

    <label for="fiscal_income_method">IRPF / estimación</label>
    <select id="fiscal_income_method" name="fiscal_income_method" required>
        <option value="estimacion_directa" @selected(old('fiscal_income_method', $company->fiscal_income_method) === 'estimacion_directa')>Estimación directa</option>
        <option value="modulos" @selected(old('fiscal_income_method', $company->fiscal_income_method) === 'modulos')>Módulos</option>
    </select>

    <label><input type="checkbox" name="fiscal_has_employees" value="1" @checked(old('fiscal_has_employees', $company->fiscal_has_employees))> Tengo empleados / practico retenciones (111)</label>
    <label><input type="checkbox" name="fiscal_has_rented_local" value="1" @checked(old('fiscal_has_rented_local', $company->fiscal_has_rented_local))> Local alquilado con retención (115)</label>
    <label><input type="checkbox" name="fiscal_reminders_opt_in" value="1" @checked(old('fiscal_reminders_opt_in', $company->fiscal_reminders_opt_in ?? true))> Email 10 días antes</label>

    <button type="submit" class="btn btn-primary">Guardar y generar calendario</button>
</form>

@if ($company->fiscal_profile_completed_at)
    <p>
        <a class="btn btn-secondary" href="{{ route('fiscal.ical', $company->fiscal_calendar_token) }}">Descargar iCal</a>
        <a href="{{ $aeatUrl }}" rel="noopener noreferrer" target="_blank">Calendario AEAT</a>
    </p>
    <table class="table">
        <thead><tr><th>Modelo</th><th>Periodo</th><th>Vence</th><th>Estimación</th></tr></thead>
        <tbody>
        @forelse ($obligations as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['period'] }}</td>
                <td>{{ $row['due'] }}</td>
                <td>
                    @if ($row['estimate'])
                        IVA {{ number_format($row['estimate']['vat'], 2, ',', '.') }} € /
                        IRPF {{ number_format($row['estimate']['irpf'], 2, ',', '.') }} €
                        ({{ $row['estimate']['invoices'] }} fact.)
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4">Sin obligaciones para este año con el perfil actual.</td></tr>
        @endforelse
        </tbody>
    </table>
@endif
@endsection
