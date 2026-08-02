<style>
    @page {
        margin: 14mm 16mm 16mm 16mm;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10.5px;
        color: #1f2937;
        line-height: 1.45;
    }

    .accent-bar {
        height: 4px;
        background: #2563eb;
        margin-bottom: 18px;
        border-radius: 2px;
    }

    .doc-header {
        width: 100%;
        margin-bottom: 22px;
        border-collapse: collapse;
    }

    .doc-header td { vertical-align: top; }

    .doc-header .brand-cell { width: 55%; padding-right: 12px; }

    .logo-wrap {
        margin-bottom: 8px;
    }

    .logo-wrap img {
        max-height: 46px;
        max-width: 160px;
    }

    .brand-name {
        font-size: 15px;
        font-weight: bold;
        color: #111827;
        margin-bottom: 2px;
    }

    .brand-meta {
        font-size: 9.5px;
        color: #6b7280;
        line-height: 1.5;
    }

    .doc-title-cell { width: 45%; text-align: right; }

    .doc-type {
        font-size: 22px;
        font-weight: bold;
        color: #2563eb;
        letter-spacing: -0.3px;
        margin-bottom: 4px;
    }

    .doc-number {
        font-size: 13px;
        font-weight: bold;
        color: #111827;
        margin-bottom: 8px;
    }

    .proforma-badge {
        display: inline-block;
        background: #fef3c7;
        color: #92400e;
        padding: 4px 10px;
        font-size: 8.5px;
        font-weight: bold;
        border-radius: 4px;
        border: 1px solid #fde68a;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .parties {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }

    .parties td.party-box {
        width: 50%;
        vertical-align: top;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 14px;
    }

    .parties td.party-spacer {
        width: 12px;
        padding: 0;
        border: none;
        background: transparent;
    }

    .party-label {
        font-size: 8.5px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #2563eb;
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid #dbeafe;
    }

    .party-name {
        font-size: 11.5px;
        font-weight: bold;
        color: #111827;
        margin-bottom: 4px;
    }

    .party-details {
        font-size: 9.5px;
        color: #4b5563;
        line-height: 1.55;
    }

    .meta-box {
        width: 100%;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 8px;
        margin-bottom: 18px;
        border-collapse: collapse;
    }

    .meta-box td {
        padding: 10px 14px;
        font-size: 9.5px;
    }

    .meta-box .meta-label {
        color: #6b7280;
        font-size: 8.5px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        display: block;
        margin-bottom: 2px;
    }

    .meta-box .meta-value {
        font-weight: bold;
        color: #111827;
    }

    table.lines {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    table.lines thead th {
        background: #2563eb;
        color: #ffffff;
        text-align: left;
        padding: 9px 10px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    table.lines thead th.num { text-align: right; }

    table.lines tbody td {
        padding: 8px 10px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 10px;
        vertical-align: top;
    }

    table.lines tbody tr:nth-child(even) td {
        background: #fafafa;
    }

    table.lines tbody tr:last-child td {
        border-bottom: none;
    }

    table.lines .num { text-align: right; white-space: nowrap; }

    table.lines .desc { color: #374151; }

    .bottom-section {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }

    .bottom-section td { vertical-align: top; }

    .notes-cell {
        width: 55%;
        padding-right: 14px;
    }

    .notes-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 9.5px;
        color: #4b5563;
        line-height: 1.55;
    }

    .notes-box strong {
        display: block;
        color: #111827;
        font-size: 8.5px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }

    .totals-cell { width: 45%; }

    table.totals {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    table.totals td {
        padding: 7px 12px;
        font-size: 10px;
    }

    table.totals .label {
        text-align: left;
        color: #6b7280;
    }

    table.totals .value {
        text-align: right;
        font-weight: bold;
        color: #374151;
    }

    table.totals .grand td {
        background: #2563eb;
        color: #ffffff;
        font-size: 12px;
        font-weight: bold;
        padding: 10px 12px;
    }

    table.totals .grand .label { color: #dbeafe; }

    table.totals .grand .value { color: #ffffff; }

    .iban-box {
        margin-top: 14px;
        padding: 12px 14px;
        background: #f0fdf4;
        border: 1px dashed #86efac;
        border-radius: 8px;
        font-size: 10px;
        color: #166534;
    }

    .iban-box strong {
        display: block;
        font-size: 8.5px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #15803d;
        margin-bottom: 4px;
    }

    .iban-value {
        font-family: DejaVu Sans Mono, DejaVu Sans, monospace;
        font-size: 11px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    .disclaimer {
        margin-top: 22px;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
        font-size: 8px;
        color: #9ca3af;
        text-align: center;
        line-height: 1.5;
    }

    .footer-brand {
        margin-top: 4px;
        font-size: 7.5px;
        color: #d1d5db;
    }
</style>
