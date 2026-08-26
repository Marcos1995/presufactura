@extends('layouts.marketing')

@section('title', $guide['title'].' — '.config('app.name'))
@section('meta_description', $guide['meta'])
@section('og_title', $guide['heading'])
@section('og_description', $guide['meta'])
@section('canonical', route('guides.show', $slug))

@section('content')
<article class="guide-page">
    <p class="guide-kicker"><a href="{{ route('guides.index') }}">Guías</a></p>
    <h1>{{ $guide['heading'] }}</h1>
    <p class="guide-lead">{{ $guide['lead'] }}</p>

    @foreach ($guide['sections'] as $section)
        <h2>{{ $section['heading'] }}</h2>
        @foreach ($section['paragraphs'] as $paragraph)
            <p>{{ $paragraph }}</p>
        @endforeach
    @endforeach

    <div class="guide-cta">
        <p>{{ $guide['cta'] }}</p>
        <a href="{{ route('register') }}" class="btn btn-primary" data-analytics="signup_cta_click">Probar PresuFactura gratis</a>
    </div>

    <aside class="guide-related">
        <h2>Más guías</h2>
        <ul>
            @foreach ($guides as $otherSlug => $other)
                @if ($otherSlug !== $slug)
                    <li><a href="{{ route('guides.show', $otherSlug) }}">{{ $other['heading'] }}</a></li>
                @endif
            @endforeach
        </ul>
    </aside>
</article>
@endsection
