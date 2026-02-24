<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Activation de compte</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f7f7f7; padding:20px;">
    <div style="max-width:500px; margin:auto; background:#fff; padding:20px; border-radius:6px;">
        <h2>Activation de votre compte</h2>

        <p>Un compte autorité a été créé pour vous.</p>

        <p>Cliquez sur le bouton ci-dessous pour activer votre compte et définir votre mot de passe :</p>

        <p style="text-align:center; margin:30px 0;">
            <a href="{{ $url }}"
               style="background:#2563eb; color:#fff; padding:12px 20px; text-decoration:none; border-radius:5px;">
                Activer mon compte
            </a>
        </p>

        <p>Ce lien est valide pendant <strong>24 heures</strong>.</p>

        <p>Si vous n’êtes pas concerné, ignorez ce message.</p>

        <hr>
        <small>© {{ date('Y') }} GUUDA</small>
    </div>
</body>
</html>
