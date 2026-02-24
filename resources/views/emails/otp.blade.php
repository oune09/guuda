<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Code de vérification</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f7f7f7; padding:20px;">
    <div style="max-width:500px; margin:auto; background:#fff; padding:20px; border-radius:6px;">
        <h2>Vérification de votre compte</h2>

        <p>Votre code de vérification est :</p>

        <h1 style="letter-spacing:4px;">{{ $otp }}</h1>

        <p>Ce code est valable pendant <strong>10 minutes</strong>.</p>

        <p>Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.</p>

        <hr>
        <small>© {{ date('Y') }} GUUDA</small>
    </div>
</body>
</html>
