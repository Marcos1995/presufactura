@php
    $href = $href ?? url('/');
    $variant = $variant ?? 'default';
    $class = trim('brand-logo brand-logo--' . $variant . ' ' . ($extraClass ?? ''));
@endphp
<a href="{{ $href }}" class="{{ $class }}">
    <img src="{{ asset('images/logo-icon.svg') }}" alt="" class="brand-logo__icon" width="32" height="32">
    <span class="brand-logo__text">{{ config('app.name') }}</span>
</a>
