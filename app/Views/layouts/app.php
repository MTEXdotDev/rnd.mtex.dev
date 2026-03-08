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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #0a0a0f;
            --surface:  #111118;
            --border:   #1e1e2e;
            --accent:   #7c3aed;
            --accent2:  #06b6d4;
            --accent3:  #f59e0b;
            --text:     #e2e8f0;
            --muted:    #64748b;
            --mono:     'Space Mono', monospace;
            --sans:     'DM Sans', sans-serif;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Grid noise overlay */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(124,58,237,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(124,58,237,.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        .wrap { position: relative; z-index: 1; }

        <?= $content_styles ?? '' ?>
    </style>
</head>
<body>
<div class="wrap">
    <?= $content ?>
</div>
</body>
</html>
