@extends('layouts.marketing')

@section('title', 'Comprobar factura PDF — Veri*Factu — '.config('app.name'))
@section('canonical', route('tools.verifactu.checker'))

@section('content')
<section class="guide-page">
    <h1>Comprobar datos de una factura</h1>
    <p class="guide-lead">Sube un PDF con texto o pega el contenido. Indicamos campos habituales que faltan. No certifica Veri*Factu.</p>
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    <form method="POST" action="{{ route('tools.verifactu.checker.run') }}" enctype="multipart/form-data">
        @csrf
        <label for="text">Texto (opcional)</label>
        <textarea id="text" name="text" rows="8" style="width:100%">{{ old('text') }}</textarea>
        <label for="pdf" style="display:block;margin-top:1rem">PDF / TXT (máx. 5 MB)</label>
        <input id="pdf" type="file" name="pdf" accept=".pdf,.txt,application/pdf,text/plain">
        <p style="margin-top:1rem"><button type="submit" class="btn btn-primary">Comprobar</button></p>
    </form>
</section>
@endsection
