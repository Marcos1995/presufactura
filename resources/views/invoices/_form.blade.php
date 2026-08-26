<div class="card">
    <form method="POST" action="{{ $action }}" class="form" id="invoice-form">
        @csrf
        @if ($method === 'PUT')
            @method('PUT')
        @endif

        <div class="form-row">
            <div class="form-group">
                <label for="client_id">Cliente *</label>
                <select id="client_id" name="client_id" required>
                    <option value="">— Seleccionar —</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id', $invoice?->client_id) == $client->id)>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
                @error('client_id')<span class="form-error">{{ $message }}</span>@enderror
                @if ($clients->isEmpty())
                    <span class="form-hint"><a href="{{ route('clients.create') }}">Crea un cliente primero</a></span>
                @endif
            </div>
            <div class="form-group">
                <label for="issue_date">Fecha emisión *</label>
                <input type="date" id="issue_date" name="issue_date"
                    value="{{ old('issue_date', $invoice?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                @error('issue_date')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="due_date">Fecha vencimiento *</label>
                <input type="date" id="due_date" name="due_date"
                    value="{{ old('due_date', $invoice?->due_date?->format('Y-m-d') ?? now()->addDays(auth()->user()->default_due_days)->format('Y-m-d')) }}" required>
                @error('due_date')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="line-items-section">
            <div class="section-header">
                <h2>Líneas</h2>
                <button type="button" id="add-line" class="btn btn-secondary btn-sm">+ Añadir línea</button>
            </div>

            <table class="line-items-table" id="line-items-table">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th class="col-qty">Cant.</th>
                        <th class="col-price">Precio</th>
                        <th class="col-vat">IVA %</th>
                        <th class="col-total">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="line-items-body">
                    @php
                        $oldLines = old('lines');
                        $rows = $oldLines ?? ($lineItems && count($lineItems) ? $lineItems : [['description' => '', 'quantity' => 1, 'unit_price' => '', 'vat_rate' => $defaultVatRate]]);
                    @endphp
                    @foreach ($rows as $i => $line)
                    <tr class="line-item-row">
                        <td>
                            <input type="text" name="lines[{{ $i }}][description]" class="line-desc"
                                value="{{ is_array($line) ? ($line['description'] ?? '') : $line->description }}" required>
                        </td>
                        <td>
                            <input type="number" name="lines[{{ $i }}][quantity]" class="line-qty" step="0.01" min="0.01"
                                value="{{ is_array($line) ? ($line['quantity'] ?? 1) : $line->quantity }}" required>
                        </td>
                        <td>
                            <input type="number" name="lines[{{ $i }}][unit_price]" class="line-price" step="0.01" min="0"
                                value="{{ is_array($line) ? ($line['unit_price'] ?? '') : $line->unit_price }}" required>
                        </td>
                        <td>
                            <input type="number" name="lines[{{ $i }}][vat_rate]" class="line-vat" step="0.01" min="0" max="100"
                                value="{{ is_array($line) ? ($line['vat_rate'] ?? $defaultVatRate) : $line->vat_rate }}" required>
                        </td>
                        <td class="line-total text-right">0,00 €</td>
                        <td><button type="button" class="btn-link btn-danger-link remove-line">✕</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @error('lines')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="totals-box">
            <div class="totals-row"><span>Subtotal</span><span id="total-subtotal">0,00 €</span></div>
            <div class="totals-row"><span>IVA</span><span id="total-vat">0,00 €</span></div>
            <div class="totals-row totals-grand"><span>Total</span><span id="total-grand">0,00 €</span></div>
        </div>

        <div class="form-group">
            <label for="notes">Notas</label>
            <textarea id="notes" name="notes" rows="2">{{ old('notes', $invoice?->notes) }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" @disabled($clients->isEmpty())>{{ $invoice ? 'Guardar cambios' : 'Crear factura' }}</button>
        </div>
    </form>
</div>

<template id="line-item-template">
    <tr class="line-item-row">
        <td><input type="text" name="lines[__INDEX__][description]" class="line-desc" required></td>
        <td><input type="number" name="lines[__INDEX__][quantity]" class="line-qty" step="0.01" min="0.01" value="1" required></td>
        <td><input type="number" name="lines[__INDEX__][unit_price]" class="line-price" step="0.01" min="0" required></td>
        <td><input type="number" name="lines[__INDEX__][vat_rate]" class="line-vat" step="0.01" min="0" max="100" value="{{ $defaultVatRate }}" required></td>
        <td class="line-total text-right">0,00 €</td>
        <td><button type="button" class="btn-link btn-danger-link remove-line">✕</button></td>
    </tr>
</template>
