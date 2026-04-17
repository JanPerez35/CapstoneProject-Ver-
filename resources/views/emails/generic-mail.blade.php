<!DOCTYPE html>
<html>
<head>
    <title>Email de MAIKINE</title>
</head>
<body>

{{--
    Email template used by GenericMail.
    This view receives:
    - $subjectText (optional, currently unused in UI)
    - $messageText (main email body content)
--}}

{{--Optional Subject Text--}}
{{--<h2>{{ $subjectText }}</h2>--}}

{{--Greeting--}}
<p>Saludos,</p>

{{-- Dynamic message content injected from EmailService / GenericMail --}}
<p>{{ $messageText }}</p>

{{-- Signature --}}
<p>Atentamente,<br>Equipo MAIKINE</p>
</body>
</html>
