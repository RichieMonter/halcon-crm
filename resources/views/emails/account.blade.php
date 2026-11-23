<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $actionText }} | Kopion CRM</title>
</head>
<body style="margin:0;background:#f1f5f9;color:#0f172a;font:16px Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f1f5f9;">
        <tr><td align="center" style="padding:20px;background:#e1f5fc;color:#17556c;font-weight:bold;">KOPION CRM</td></tr>
        <tr><td align="center" style="padding:28px 12px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;">
                <tr><td style="padding:28px;line-height:1.6;">
                    <img src="{{ $message->embed(public_path('images/logo.png')) }}" width="80" alt="Papelería Kopion" style="display:block;margin:0 auto 16px;">
                    <h1 style="font-size:23px;line-height:1.3;margin:0 0 20px;text-align:center;">{{ $actionText }}</h1>
                    <p>¡Hola!</p>
                    @foreach ($introLines as $line)
                        <p>{{ $line }}</p>
                    @endforeach
                    <p style="text-align:center;margin:24px 0;">
                        <a href="{{ $actionUrl }}" style="display:inline-block;padding:12px 20px;border-radius:10px;background:#5cb9db;color:#1a3443;font-weight:bold;text-decoration:none;">{{ $actionText }}</a>
                    </p>
                    @foreach ($outroLines as $line)
                        <p>{{ $line }}</p>
                    @endforeach
                    <p>Saludos,<br>El equipo de Kopion CRM</p>
                    <p style="border-top:1px solid #e2e8f0;padding-top:14px;color:#475569;font-size:13px;">
                        Si el botón no funciona, copia este enlace y pégalo en tu navegador.<br>
                        <a href="{{ $actionUrl }}" style="color:#3730a3;word-break:break-all;">{{ $actionUrl }}</a>
                    </p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
