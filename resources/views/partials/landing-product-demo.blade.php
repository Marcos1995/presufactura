<div class="hero-demo reveal reveal-delay-2">
    <p class="hero-demo-kicker">Así funciona · 24 s</p>
    <div class="screenshot-mock" data-mock-autoplay="6000">
        <div class="mock-sidebar">
            <span class="mock-brand">
                <img src="{{ asset('images/logo-icon.svg') }}" alt="" width="24" height="24">
                PresuFactura
            </span>
            <button type="button" class="mock-nav active" data-mock-tab="quotes">Presupuesto</button>
            <button type="button" class="mock-nav" data-mock-tab="invoices">Factura</button>
            <button type="button" class="mock-nav" data-mock-tab="verifactu">Veri*Factu</button>
            <button type="button" class="mock-nav" data-mock-tab="dashboard">Cobro</button>
        </div>
        <div class="mock-main">
            <div data-mock-panel="quotes">
                <div class="mock-panel-title">El cliente acepta el presupuesto</div>
                <div class="mock-table">
                    <div class="mock-row head"><span>Número</span><span>Cliente</span><span>Estado</span><span>Total</span></div>
                    <div class="mock-row"><span>PRE-2026-008</span><span>López Design</span><span class="badge-accepted">Aceptado</span><span>890 €</span></div>
                    <div class="mock-row"><span>PRE-2026-007</span><span>TechStart</span><span class="badge-sent">Enviado</span><span>2.400 €</span></div>
                    <div class="mock-row"><span>PRE-2026-006</span><span>Consulting Pro</span><span class="badge-sent">Enviado</span><span>650 €</span></div>
                </div>
            </div>
            <div data-mock-panel="invoices" hidden>
                <div class="mock-panel-title">Un clic: presupuesto → factura + PDF</div>
                <div class="mock-table">
                    <div class="mock-row head"><span>Número</span><span>Cliente</span><span>Estado</span><span>Total</span></div>
                    <div class="mock-row"><span>FAC-2026-012</span><span>Acme SL</span><span class="badge-sent">Enviada</span><span>1.210 €</span></div>
                    <div class="mock-row"><span>FAC-2026-011</span><span>Studio Norte</span><span class="badge-paid">Pagada</span><span>450 €</span></div>
                    <div class="mock-row"><span>FAC-2026-010</span><span>María Ruiz</span><span class="badge-sent">Enviada</span><span>320 €</span></div>
                </div>
            </div>
            <div data-mock-panel="verifactu" hidden>
                <div class="mock-panel-title">QR tributario en la factura</div>
                <div class="mock-verifactu">
                    <svg class="mock-qr" viewBox="0 0 29 29" width="88" height="88" aria-hidden="true">
                        <rect width="29" height="29" fill="#fff"/>
                        <g fill="#111">
                            <rect x="2" y="2" width="7" height="7"/>
                            <rect x="20" y="2" width="7" height="7"/>
                            <rect x="2" y="20" width="7" height="7"/>
                            <rect x="4" y="4" width="3" height="3" fill="#fff"/>
                            <rect x="22" y="4" width="3" height="3" fill="#fff"/>
                            <rect x="4" y="22" width="3" height="3" fill="#fff"/>
                            <rect x="11" y="3" width="2" height="2"/>
                            <rect x="15" y="2" width="2" height="3"/>
                            <rect x="12" y="7" width="3" height="2"/>
                            <rect x="18" y="6" width="2" height="2"/>
                            <rect x="11" y="11" width="2" height="2"/>
                            <rect x="14" y="12" width="3" height="2"/>
                            <rect x="18" y="11" width="2" height="3"/>
                            <rect x="21" y="13" width="2" height="2"/>
                            <rect x="24" y="11" width="3" height="2"/>
                            <rect x="3" y="12" width="2" height="2"/>
                            <rect x="6" y="14" width="3" height="2"/>
                            <rect x="11" y="16" width="2" height="3"/>
                            <rect x="15" y="17" width="2" height="2"/>
                            <rect x="18" y="16" width="3" height="2"/>
                            <rect x="22" y="18" width="2" height="2"/>
                            <rect x="25" y="20" width="2" height="3"/>
                            <rect x="12" y="21" width="2" height="2"/>
                            <rect x="15" y="23" width="3" height="2"/>
                            <rect x="11" y="25" width="2" height="2"/>
                            <rect x="19" y="24" width="2" height="3"/>
                        </g>
                    </svg>
                    <div>
                        <strong>VERI*FACTU</strong>
                        <p>Factura verificable en la sede electrónica de la AEAT</p>
                    </div>
                </div>
            </div>
            <div data-mock-panel="dashboard" hidden>
                <div class="mock-panel-title">Sabes qué está por cobrar</div>
                <div class="mock-stats">
                    <div class="mock-stat"><span>Por cobrar</span><strong data-count-to="2450" data-count-suffix=" €" data-count-decimals="0">0 €</strong></div>
                    <div class="mock-stat danger"><span>Vencido</span><strong data-count-to="380" data-count-suffix=" €" data-count-decimals="0">0 €</strong></div>
                    <div class="mock-stat success"><span>Cobrado mes</span><strong data-count-to="5120" data-count-suffix=" €" data-count-decimals="0">0 €</strong></div>
                    <div class="mock-stat"><span>Docs mes</span><strong data-count-to="12" data-count-decimals="0">0</strong></div>
                </div>
                <div class="mock-table">
                    <div class="mock-row head"><span>Número</span><span>Cliente</span><span>Estado</span><span>Total</span></div>
                    <div class="mock-row"><span>FAC-2026-012</span><span>Acme SL</span><span class="badge-sent">Enviada</span><span>1.210 €</span></div>
                    <div class="mock-row"><span>PRE-2026-008</span><span>López Design</span><span class="badge-accepted">Aceptado</span><span>890 €</span></div>
                    <div class="mock-row"><span>FAC-2026-011</span><span>Studio Norte</span><span class="badge-paid">Pagada</span><span>450 €</span></div>
                </div>
            </div>
        </div>
        <div class="hero-demo-progress" aria-hidden="true"><span></span></div>
    </div>
    <p class="hero-demo-caption">Presupuesto → factura → QR Hacienda → cobro. Sin instalar nada.</p>
</div>
