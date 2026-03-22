{{-- Ignore this, this was me testing the email service it is for me to reference later--}}
<!DOCTYPE html>
<html>
<head>
    <title>Send Email</title>
</head>
<body>

<h2>Send Email</h2>

@if(session('success'))
    <p style="color: green">{{ session('success') }}</p>
@endif

<form method="POST" action="/send-email">
    @csrf

    <label>Email:</label>
    <input type="email" name="email" required>

    <br><br>

    <label>Subject:</label>
    <input type="text" name="subject" required>

    <br><br>

    <br><br>

    <label>Message:</label>
    <textarea name="message" required></textarea>

    <br><br>

    <button type="submit">Send</button>
</form>

</body>
</html>
