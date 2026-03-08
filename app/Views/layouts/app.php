<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'rnd.mtex.dev') ?></title>
    <meta name="description" content="<?= attr($description ?? 'Random Data API by MTEX.dev') ?>">
    <meta property="og:title" content="<?= attr($title ?? 'rnd.mtex.dev') ?>">
    <meta property="og:description" content="<?= attr($description ?? '') ?>">
    <meta name="theme-color" content="#0a0a0f">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="wrap">
    <?= $content ?>
</div>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
