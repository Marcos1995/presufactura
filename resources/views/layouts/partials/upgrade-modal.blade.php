<div class="modal-overlay" id="upgrade-modal" style="display:none">
    <div class="modal-card">
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
