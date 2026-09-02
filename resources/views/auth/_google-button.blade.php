<a href="{{ route('auth.google') }}" class="btn btn-google btn-block"@if (! empty($analyticsEvent)) data-analytics="{{ $analyticsEvent }}"@endif>
    <svg class="btn-google__icon" viewBox="0 0 24 24" aria-hidden="true">
        <path fill="#4285F4" d="M23.52 12.27c0-.86-.07-1.49-.22-2.14H12v3.89h6.47c-.13 1.11-.83 2.78-2.39 3.9l-.02.14 3.47 2.69.24.02c2.21-2.04 3.49-5.04 3.49-8.5z"/>
        <path fill="#34A853" d="M12 24c3.24 0 5.96-1.07 7.95-2.91l-3.79-2.94c-1.02.71-2.39 1.21-4.16 1.21-3.18 0-5.88-2.09-6.84-4.98l-.13.01-3.7 2.87-.05.13C2.25 21.3 6.81 24 12 24z"/>
        <path fill="#FBBC05" d="M5.16 14.38A7.23 7.23 0 0 1 4.77 12c0-.83.15-1.63.39-2.38l-.01-.14-3.75-2.91-.12.06A11.96 11.96 0 0 0 0 12c0 1.94.46 3.77 1.28 5.37l3.88-2.99z"/>
        <path fill="#EA4335" d="M12 4.75c2.25 0 3.77.97 4.64 1.78l3.39-3.31C17.95 1.19 15.24 0 12 0 6.81 0 2.25 2.7.28 6.63l3.88 2.99C6.12 6.73 8.82 4.75 12 4.75z"/>
    </svg>
    Continuar con Google
</a>
<p class="auth-divider"><span>o con email</span></p>

