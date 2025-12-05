<?php
/**
 * PAGE 500 - NOVA Événements
 */
$nonce = \App\Core\Security::getNonce();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur serveur - NOVA</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style nonce="<?= $nonce ?>">
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 50%, #16213e 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 8rem;
            font-weight: 900;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>500</h1>
        <h2>Erreur serveur</h2>
        <p>Une erreur technique est survenue. Veuillez réessayer plus tard.</p>
        <a href="<?= url('/') ?>" class="btn">Retour à l'accueil</a>
    </div>
</body>
</html>
