<!DOCTYPE html>
<html>
<head>
    <title>Email de MAIKINE</title>
</head>
<body>

{{--Greeting--}}
<p>Saludos,</p>

{{-- Dynamic message content injected from EmailService / GenericMail --}}
<p>{!! nl2br(e($messageText)) !!}</p>

{{-- Signature --}}
<p>Atentamente,<br>Equipo MAIKINE</p>

</body>
</html>
