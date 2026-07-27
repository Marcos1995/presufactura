<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('email_title', config('app.name'))</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                <tr>
                    <td style="background:#2563eb;padding:20px 28px;">
                        <span style="font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.02em;">PresuFactura</span>
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
