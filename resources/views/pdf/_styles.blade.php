<style>
    @page {
        margin: 18mm 0 20mm 0;
    }

    html, body {
        margin: 0;
        padding: 0;
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #1e293b;
        line-height: 1.5;
    }

    .page-frame {
        width: 100%;
        border-collapse: collapse;
    }

    .page-gutter {
        width: 7%;
        padding: 0;
        margin: 0;
        font-size: 1px;
        line-height: 1px;
    }

    .page-content {
        width: 86%;
        vertical-align: top;
        padding: 10px 0 16px 0;
    }

    .spacer-row td {
        height: 16px;
        font-size: 1px;
        line-height: 16px;
    }

    .spacer-row-lg td {
        height: 22px;
        font-size: 1px;
        line-height: 22px;
    }

    .spacer-top td {
        height: 28px;
        font-size: 1px;
        line-height: 28px;
    }

    .spacer-bottom td {
        height: 20px;
        font-size: 1px;
        line-height: 20px;
    }

    /* Header */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        border-bottom: 2px solid #2563eb;
        padding-bottom: 14px;
    }

    .header-table td { vertical-align: top; }

    .header-left { width: 58%; padding: 0 16px 14px 0; }

    .header-right {
        width: 42%;
        text-align: right;
        padding: 0 0 14px 0;
    }

    .logo img {
        max-height: 42px;
        max-width: 150px;
        margin-bottom: 8px;
    }

    .issuer-name {
        font-size: 14px;
        font-weight: bold;
        color: #0f172a;
        margin-bottom: 3px;
    }

    .issuer-line {
        font-size: 9px;
        color: #64748b;
        line-height: 1.55;
    }

    .doc-kicker {
        font-size: 8px;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #64748b;
        font-weight: bold;
        margin-bottom: 4px;
    }

    .doc-title {
        font-size: 24px;
        font-weight: bold;
        color: #2563eb;
        line-height: 1.1;
        margin-bottom: 6px;
    }

    .doc-number {
        font-size: 12px;
        font-weight: bold;
        color: #0f172a;
        margin-bottom: 10px;
    }

    .badge {
        display: inline-block;
        padding: 5px 10px;
        font-size: 7.5px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .badge-proforma {
        background: #fff7ed;
        color: #9a3412;
        border: 1px solid #fed7aa;
    }

    .badge-fiscal {
        background: #ecfdf5;
        color: #166534;
        border: 1px solid #86efac;
    }

    /* Parties */
    .parties-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 12px 0;
    }

    .parties-table td {
        width: 50%;
        vertical-align: top;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 14px 16px;
    }

    .box-label {
        font-size: 7.5px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #2563eb;
        margin-bottom: 8px;
    }

    .box-name {
        font-size: 11px;
        font-weight: bold;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .box-text {
        font-size: 9px;
        color: #475569;
        line-height: 1.6;
    }

    /* Meta strip */
    .meta-table {
        width: 100%;
        border-collapse: collapse;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
    }

    .meta-table td {
        padding: 12px 16px;
        font-size: 9px;
        vertical-align: top;
    }

    .meta-label {
        display: block;
        font-size: 7px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        font-weight: bold;
        margin-bottom: 3px;
    }

    .meta-value {
        font-size: 10px;
        font-weight: bold;
        color: #0f172a;
    }

    .meta-total {
        font-size: 13px;
        font-weight: bold;
        color: #2563eb;
    }

    /* Lines */
    .lines-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #cbd5e1;
    }

    .lines-table thead th {
        background: #1e40af;
        color: #ffffff;
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 12px;
        text-align: left;
    }

    .lines-table thead th.r { text-align: right; }

    .lines-table tbody td {
        padding: 10px 12px;
        font-size: 9.5px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }

    .lines-table tbody tr.alt td { background: #f8fafc; }

    .lines-table tbody tr:last-child td { border-bottom: none; }

    .lines-table .r { text-align: right; white-space: nowrap; }

    /* Bottom */
    .bottom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .bottom-table td { vertical-align: top; }

    .notes-area {
        width: 54%;
        padding-right: 18px;
    }

    .totals-area { width: 46%; }

    .notes-inner {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 14px 16px;
        font-size: 9px;
        color: #475569;
        line-height: 1.6;
    }

    .notes-inner strong {
        display: block;
        font-size: 7.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .totals-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #cbd5e1;
    }

    .totals-table td {
        padding: 9px 14px;
        font-size: 9.5px;
    }

    .totals-table .lbl { color: #64748b; }

    .totals-table .val {
        text-align: right;
        font-weight: bold;
        color: #334155;
    }

    .totals-table .grand td {
        background: #2563eb;
        color: #ffffff;
        font-size: 11px;
        font-weight: bold;
        padding: 12px 14px;
    }

    .totals-table .grand .lbl { color: #dbeafe; }

    .totals-table .grand .val { color: #ffffff; }

    .iban-inner {
        background: #f0fdf4;
        border: 1px solid #86efac;
        padding: 14px 16px;
        font-size: 9px;
        color: #166534;
    }

    .iban-inner strong {
        display: block;
        font-size: 7.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .iban-code {
        font-family: DejaVu Sans Mono, DejaVu Sans, monospace;
        font-size: 11px;
        font-weight: bold;
        letter-spacing: 0.4px;
    }

    .qr-table {
        border: 1px solid #e2e8f0;
        border-radius: 4px;
    }

    .qr-cell {
        width: 90px;
        padding: 8px;
        vertical-align: middle;
    }

    .qr-image {
        width: 80px;
        height: 80px;
    }

    .qr-legend {
        font-size: 8px;
        color: #64748b;
        vertical-align: middle;
        padding: 8px 12px;
    }

    .footer {
        border-top: 1px solid #e2e8f0;
        padding-top: 14px;
        text-align: center;
        font-size: 7.5px;
        color: #94a3b8;
        line-height: 1.55;
    }

    .footer-site {
        margin-top: 4px;
        color: #cbd5e1;
        font-size: 7px;
    }
</style>
