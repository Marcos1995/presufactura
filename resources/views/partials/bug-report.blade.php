<button type="button" class="bug-report-btn" id="bug-report-open" aria-haspopup="dialog" aria-controls="bug-report-modal">
    Reportar un fallo
</button>
<div class="modal-overlay" id="bug-report-modal" hidden>
    <div class="modal-card" role="dialog" aria-labelledby="bug-report-title" aria-modal="true">
        <h2 id="bug-report-title">¿Qué ha fallado?</h2>
        <p class="text-muted">Lo enviamos a facturas@presufactura.es y queda registrado. Describe qué estabas haciendo y qué viste.</p>
        <form method="POST" action="{{ route('bug-reports.store') }}" class="form">
            @csrf
            <input type="hidden" name="page_url" id="bug-report-url" value="{{ url()->current() }}">
            <div class="form-group">
                <label for="bug-report-description">Descripción</label>
                <textarea id="bug-report-description" name="description" rows="5" required minlength="10" maxlength="4000" placeholder="Ej.: al enviar la factura no aparece el PDF…"></textarea>
                @error('description')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="bug-report-close">Cancelar</button>
                <button type="submit" class="btn btn-primary">Enviar aviso</button>
            </div>
        </form>
    </div>
</div>
