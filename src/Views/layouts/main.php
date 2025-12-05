<?php
/**
 * LAYOUT PRINCIPAL - NOVA Événements
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= e($title ?? 'NOVA ÉVÉNEMENTS') ?></title>
    
    <!-- Preconnect pour les performances -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    
    <!-- Meta SEO -->
    <meta name="description" content="<?= e($description ?? 'NOVA Événements - Agence événementielle 360°') ?>">
</head>
<body>
    <?php partial('header', ['isLoggedIn' => $isLoggedIn ?? false, 'userName' => $userName ?? '', 'userRole' => $userRole ?? '']); ?>
    
    <main>
        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>
        
        <?= $content ?? '' ?>
    </main>
    
    <?php partial('footer'); ?>
    
    <!-- Scripts avec nonce CSP -->
    <script nonce="<?= $nonce ?>">
        <?php if (isset($inlineJs)): ?>
            <?= $inlineJs ?>
        <?php endif; ?>
    </script>
    <script src="<?= asset('js/navbar.js') ?>" nonce="<?= $nonce ?>"></script>
    <?php if (!empty($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= asset($script) ?>" nonce="<?= $nonce ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
