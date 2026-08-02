<div class="modal-overlay" id="upgrade-modal" style="display:none">
    <div class="modal-card">
        <div class="modal-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <h2>Límite del plan Free</h2>
        <p>Has alcanzado el límite de <strong>3 documentos al mes</strong> del plan Free.</p>
        <p>Actualiza a Pro por <strong>12 €/mes</strong> para documentos ilimitados y recordatorios automáticos.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" id="upgrade-modal-close">Cerrar</button>
            <form method="POST" action="{{ route('stripe.checkout') }}" class="inline-form">
                @csrf
                <button type="submit" class="btn btn-primary">Actualizar a Pro</button>
            </form>
        </div>
    </div>
</div>
