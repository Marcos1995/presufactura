@extends('layouts.marketing')

@section('title', '¿Qué me pide Hacienda? — '.config('app.name'))
@section('meta_description', 'Explica en claro una carta de la AEAT o Seguridad Social. Orientativo, sin guardar el documento.')
@section('canonical', route('tools.cartas'))

@section('content')
<section class="guide-page">
    <h1>¿Qué me pide Hacienda?</h1>
    <p class="guide-lead">Pega el texto o sube un PDF. Explicamos qué es, qué hacer y el plazo si aparece. No guardamos la carta.</p>
    <p class="alert alert-warning">Orientativo. No es asesoramiento fiscal ni jurídico. En embargos solo explicamos el trámite administrativo.</p>
    @if (!empty($error)) <div class="alert alert-danger">{{ $error }}</div> @endif
    <form method="POST" action="{{ route('tools.cartas.explain') }}" enctype="multipart/form-data">
        @csrf
        <label for="text">Texto de la notificación</label>
        <textarea id="text" name="text" rows="10" style="width:100%" placeholder="Pega aquí…">{{ old('text') }}</textarea>
        <label for="file" style="display:block;margin-top:1rem">PDF / TXT / imagen (máx. 5 MB)</label>
        <input id="file" type="file" name="file" accept=".pdf,.txt,.jpg,.jpeg,.png,.webp">
        <p style="margin-top:1rem"><button type="submit" class="btn btn-primary">Explicar</button></p>
    </form>
</section>
@endsection
