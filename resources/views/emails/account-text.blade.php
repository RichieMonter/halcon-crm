{{ $actionText }} | Kopion CRM

¡Hola!

@foreach ($introLines as $line)
{{ $line }}

@endforeach
{{ $actionText }}
{!! $actionUrl !!}

@foreach ($outroLines as $line)
{{ $line }}

@endforeach
Saludos,
El equipo de Kopion CRM
