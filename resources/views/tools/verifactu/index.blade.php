@extends('layouts.marketing')

@section('title', 'Asistente Veri*Factu — '.config('app.name'))
@section('meta_description', 'Test gratuito: ¿te afecta Veri*Factu antes de 2027? Orientativo, sin afirmar certificación oficial.')
@section('canonical', route('tools.verifactu'))

@section('content')
<section class="guide-page">
    <h1>Asistente Veri*Factu</h1>
    <p class="guide-lead">{{ $deadlineNote }}</p>
    <p class="alert alert-warning">Orientativo. No es un dictamen oficial ni certificación del software ante la AEAT.</p>

    <form method="POST" action="{{ route('tools.verifactu.quiz') }}" class="stack-form">
        @csrf
        @foreach ($questions as $q)
            <fieldset style="margin-bottom:1.25rem">
                <legend><strong>{{ $q['text'] }}</strong></legend>
                @foreach ($q['options'] as $value => $label)
                    <label style="display:block;margin:.35rem 0">
                        <input type="radio" name="{{ $q['id'] }}" value="{{ $value }}" required @checked(old($q['id']) === $value)>
                        {{ $label }}
                    </label>
                @endforeach
                @error($q['id']) <div class="alert alert-danger">{{ $message }}</div> @enderror
            </fieldset>
        @endforeach
        <button type="submit" class="btn btn-primary">Ver resultado</button>
        <a href="{{ route('tools.verifactu.checker') }}" class="btn btn-secondary">Comprobar un PDF</a>
    </form>

    <p style="margin-top:1.5rem"><a href="{{ $aeatUrl }}" rel="noopener noreferrer" target="_blank">Documentación AEAT Veri*Factu</a></p>
</section>
@endsection
