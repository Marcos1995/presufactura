<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <title>@yield('email_title', config('app.name'))</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Plus Jakarta Sans',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,0.08);">
                <tr>
                    <td style="background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 50%,#3b82f6 100%);padding:22px 28px;">
                        <table role="presentation" cellpadding="0" cellspacing="0"><tr>
                            <td style="padding-right:12px;vertical-align:middle;">
                                <img src="{{ asset('images/logo-icon.svg') }}" alt="" width="36" height="36" style="display:block;border-radius:8px;">
                            </td>
                            <td style="vertical-align:middle;">
                                <span style="font-size:20px;font-weight:800;color:#ffffff;letter-spacing:-0.02em;">PresuFactura</span>
                            </td>
                        </tr></table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;color:#111827;font-size:15px;line-height:1.6;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 28px 24px;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;line-height:1.5;">
                        Documento proforma · Sin Verifactu v1<br>
                        <a href="{{ config('app.url') }}" style="color:#2563eb;">presufactura.es</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
