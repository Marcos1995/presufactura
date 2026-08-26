@if ($showFeedbackPrompt ?? false)
<div class="feedback-prompt card">
    <h2>¿Cómo te está yendo?</h2>
    <p>Tras tu primer documento nos ayuda saber esto. No pedimos datos de clientes ni importes.</p>
    <form method="POST" action="{{ route('feedback.store') }}" class="form">
        @csrf
        <div class="form-group">
            <label for="expected">¿Qué esperabas encontrar?</label>
            <textarea id="expected" name="expected" rows="2" maxlength="2000"></textarea>
        </div>
        <div class="form-group">
            <label for="difficult">¿Qué te resultó difícil?</label>
            <textarea id="difficult" name="difficult" rows="2" maxlength="2000"></textarea>
        </div>
        <div class="form-group">
            <label for="used_before">¿Qué utilizabas antes?</label>
            <input type="text" id="used_before" name="used_before" maxlength="2000">
        </div>
        <div class="form-group">
            <label for="missing_weekly">¿Qué te falta para usarlo cada semana?</label>
            <textarea id="missing_weekly" name="missing_weekly" rows="2" maxlength="2000"></textarea>
        </div>
        <div class="form-group">
            <label for="would_recommend">¿Lo recomendarías a otro autónomo? (0–10)</label>
            <input type="number" id="would_recommend" name="would_recommend" min="0" max="10">
        </div>
        <div class="empty-actions">
            <button type="submit" class="btn btn-primary">Enviar</button>
            <button type="submit" name="dismiss" value="1" class="btn btn-secondary">Ahora no</button>
        </div>
    </form>
</div>
@endif
