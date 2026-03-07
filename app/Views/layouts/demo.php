<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Demo') ?> · PHP-Zero Demo</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        /* ── Demo shell ──────────────────────────────────────────────────── */
        .demo-shell {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        .demo-topbar {
            background: #0a0f1e;
            border-bottom: 1px solid #1e293b;
            padding: .625rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 200;
        }

        .demo-topbar__brand {
            font-weight: 700;
            font-size: .9375rem;
            color: #38bdf8;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .5rem;
            letter-spacing: -.3px;
        }

        .demo-topbar__brand:hover { text-decoration: none; }

        .demo-topbar__badge {
            font-size: .6875rem;
            font-weight: 600;
            background: rgba(129,140,248,.15);
            border: 1px solid rgba(129,140,248,.3);
            color: #818cf8;
            padding: .2em .6em;
            border-radius: 999px;
            letter-spacing: .04em;
        }

        .demo-topbar__back {
            font-size: .8125rem;
            color: #64748b;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .3rem;
            transition: color .15s;
        }

        .demo-topbar__back:hover { color: #94a3b8; text-decoration: none; }

        /* ── Body grid ───────────────────────────────────────────────────── */
        .demo-body {
            display: grid;
            grid-template-columns: 220px 1fr;
            flex: 1;
            min-height: 0;
        }

        @media (max-width: 768px) {
            .demo-body { grid-template-columns: 1fr; }
            .demo-sidebar { display: none; }
        }

        /* ── Sidebar ─────────────────────────────────────────────────────── */
        .demo-sidebar {
            background: #0d1526;
            border-right: 1px solid #1e293b;
            padding: 1.5rem 0;
            position: sticky;
            top: 43px;
            height: calc(100vh - 43px);
            overflow-y: auto;
        }

        .demo-sidebar__section {
            padding: 0 1rem .75rem;
            margin-bottom: .25rem;
        }

        .demo-sidebar__label {
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #334155;
            padding: 0 .5rem .5rem;
        }

        .demo-sidebar__link {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem .5rem;
            border-radius: .4rem;
            font-size: .8125rem;
            color: #64748b;
            text-decoration: none;
            transition: background .12s, color .12s;
            line-height: 1.4;
        }

        .demo-sidebar__link:hover {
            background: rgba(255,255,255,.04);
            color: #94a3b8;
            text-decoration: none;
        }

        .demo-sidebar__link.active {
            background: rgba(56,189,248,.08);
            color: #38bdf8;
        }

        .demo-sidebar__link svg { flex-shrink: 0; opacity: .7; }
        .demo-sidebar__link.active svg { opacity: 1; }

        /* ── Main panel ──────────────────────────────────────────────────── */
        .demo-main {
            padding: 2rem 2.5rem;
            max-width: 860px;
            overflow-x: hidden;
        }

        .demo-breadcrumb {
            display: flex;
            align-items: center;
            gap: .375rem;
            font-size: .75rem;
            color: #475569;
            margin-bottom: 1.5rem;
        }

        .demo-breadcrumb a { color: #64748b; text-decoration: none; }
        .demo-breadcrumb a:hover { color: #94a3b8; }

        .demo-heading {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -.5px;
            color: #f1f5f9;
            margin-bottom: .5rem;
        }

        .demo-subheading {
            color: #64748b;
            font-size: .9375rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        /* ── Demo cards / sections ───────────────────────────────────────── */
        .demo-section {
            margin-bottom: 2.5rem;
        }

        .demo-section__title {
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: .875rem;
            padding-bottom: .5rem;
            border-bottom: 1px solid #1e293b;
        }

        .demo-panel {
            background: #131c2e;
            border: 1px solid #1e293b;
            border-radius: .625rem;
            overflow: hidden;
        }

        .demo-panel__header {
            background: #0a0f1e;
            border-bottom: 1px solid #1e293b;
            padding: .625rem 1rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            font-size: .75rem;
            color: #475569;
            font-family: 'JetBrains Mono', monospace;
        }

        .demo-panel__header .method {
            font-weight: 700;
            padding: .1em .5em;
            border-radius: .25rem;
            font-size: .6875rem;
        }

        .method-get    { background: rgba(34,197,94,.15); color: #4ade80; }
        .method-post   { background: rgba(251,191,36,.15); color: #fbbf24; }
        .method-put    { background: rgba(56,189,248,.15); color: #38bdf8; }
        .method-delete { background: rgba(251,113,133,.15); color: #fb7185; }

        .demo-panel__body {
            padding: 1.25rem;
        }

        /* ── Code blocks ─────────────────────────────────────────────────── */
        .code-block {
            background: #0a0f1e;
            border: 1px solid #1e293b;
            border-radius: .5rem;
            padding: 1rem 1.25rem;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: .7813rem;
            line-height: 1.8;
            color: #94a3b8;
            overflow-x: auto;
            margin: .75rem 0;
        }

        .code-block .t-kw  { color: #c084fc; }
        .code-block .t-fn  { color: #38bdf8; }
        .code-block .t-str { color: #4ade80; }
        .code-block .t-cmt { color: #334155; font-style: italic; }
        .code-block .t-cls { color: #fbbf24; }
        .code-block .t-num { color: #fb923c; }
        .code-block .t-var { color: #e2e8f0; }
        .code-block .t-key { color: #38bdf8; }

        /* ── Result boxes ────────────────────────────────────────────────── */
        .result-box {
            background: rgba(56,189,248,.05);
            border: 1px solid rgba(56,189,248,.15);
            border-radius: .5rem;
            padding: 1rem 1.25rem;
            margin-top: .75rem;
        }

        .result-box__label {
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #38bdf8;
            margin-bottom: .5rem;
        }

        .result-box__value {
            font-family: 'JetBrains Mono', monospace;
            font-size: .8125rem;
            color: #cbd5e1;
            word-break: break-all;
        }

        /* ── KV table ────────────────────────────────────────────────────── */
        .kv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .8125rem;
        }

        .kv-table tr { border-bottom: 1px solid #1e293b; }
        .kv-table tr:last-child { border-bottom: none; }

        .kv-table td {
            padding: .5rem .75rem;
            vertical-align: top;
        }

        .kv-table td:first-child {
            font-family: 'JetBrains Mono', monospace;
            font-size: .75rem;
            color: #64748b;
            white-space: nowrap;
            width: 35%;
        }

        .kv-table td:last-child {
            color: #e2e8f0;
            font-family: 'JetBrains Mono', monospace;
            word-break: break-all;
        }

        /* ── Tags ────────────────────────────────────────────────────────── */
        .tag {
            display: inline-block;
            font-size: .6875rem;
            font-weight: 600;
            padding: .2em .55em;
            border-radius: .25rem;
            letter-spacing: .03em;
        }

        .tag-blue   { background: rgba(56,189,248,.12); color: #38bdf8; }
        .tag-green  { background: rgba(34,197,94,.12);  color: #4ade80; }
        .tag-violet { background: rgba(129,140,248,.12); color: #818cf8; }
        .tag-amber  { background: rgba(251,191,36,.12);  color: #fbbf24; }
        .tag-rose   { background: rgba(251,113,133,.12); color: #fb7185; }
        .tag-muted  { background: rgba(100,116,139,.12); color: #64748b; }

        /* ── Forms ───────────────────────────────────────────────────────── */
        .demo-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 600px) { .demo-form .form-row { grid-template-columns: 1fr; } }

        .field-error {
            font-size: .75rem;
            color: #f87171;
            margin-top: .3rem;
        }

        .input-error { border-color: #ef4444 !important; }

        /* ── Nav grid on demo index ──────────────────────────────────────── */
        .demo-nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .demo-nav-card {
            background: #131c2e;
            border: 1px solid #1e293b;
            border-radius: .625rem;
            padding: 1.25rem;
            text-decoration: none;
            display: block;
            transition: border-color .15s, transform .12s;
        }

        .demo-nav-card:hover {
            border-color: #38bdf8;
            transform: translateY(-2px);
            text-decoration: none;
        }

        .demo-nav-card__icon {
            width: 2rem;
            height: 2rem;
            border-radius: .375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: .75rem;
        }

        .demo-nav-card__title {
            font-size: .9375rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: .25rem;
        }

        .demo-nav-card__desc {
            font-size: .75rem;
            color: #475569;
            line-height: 1.5;
        }

        /* ── Str demo grid ───────────────────────────────────────────────── */
        .str-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
        }

        @media (max-width: 600px) { .str-grid { grid-template-columns: 1fr; } }

        .str-row {
            background: #0a0f1e;
            border: 1px solid #1e293b;
            border-radius: .375rem;
            padding: .5rem .75rem;
            display: flex;
            flex-direction: column;
            gap: .2rem;
        }

        .str-row__call { font-size: .7rem; color: #475569; font-family: monospace; }
        .str-row__result { font-size: .8125rem; color: #38bdf8; font-family: monospace; word-break: break-all; }
    </style>
</head>
<body class="demo-shell">

<header class="demo-topbar">
    <a class="demo-topbar__brand" href="/demo">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
        </svg>
        PHP-Zero
        <span class="demo-topbar__badge">Demo</span>
    </a>
    <a class="demo-topbar__back" href="/">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
        Back to home
    </a>
</header>

<div class="demo-body">

    <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
    <aside class="demo-sidebar">

        <div class="demo-sidebar__section">
            <div class="demo-sidebar__label">Overview</div>
            <a class="demo-sidebar__link <?= $activeSection === 'index'    ? 'active' : '' ?>" href="/demo">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                All Demos
            </a>
        </div>

        <div class="demo-sidebar__section">
            <div class="demo-sidebar__label">Routing</div>
            <a class="demo-sidebar__link <?= $activeSection === 'routing'  ? 'active' : '' ?>" href="/demo/routing">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/></svg>
                Router &amp; Params
            </a>
            <a class="demo-sidebar__link <?= $activeSection === 'slug'     ? 'active' : '' ?>" href="/demo/slug/hello-world">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                Slug Route
            </a>
            <a class="demo-sidebar__link <?= $activeSection === 'user'     ? 'active' : '' ?>" href="/demo/user/42">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                User by ID
            </a>
        </div>

        <div class="demo-sidebar__section">
            <div class="demo-sidebar__label">Request</div>
            <a class="demo-sidebar__link <?= $activeSection === 'query'    ? 'active' : '' ?>" href="/demo/query?search=php-zero&sort=name&page=2">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Query Strings
            </a>
            <a class="demo-sidebar__link <?= $activeSection === 'forms'    ? 'active' : '' ?>" href="/demo/forms">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="12" y2="17"/></svg>
                Form + Validation
            </a>
        </div>

        <div class="demo-sidebar__section">
            <div class="demo-sidebar__label">Features</div>
            <a class="demo-sidebar__link <?= $activeSection === 'database' ? 'active' : '' ?>" href="/demo/database">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/></svg>
                Database (SQLite)
            </a>
            <a class="demo-sidebar__link <?= $activeSection === 'cache'    ? 'active' : '' ?>" href="/demo/cache">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Cache
            </a>
            <a class="demo-sidebar__link <?= $activeSection === 'str'      ? 'active' : '' ?>" href="/demo/str">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                Str Utilities
            </a>
            <a class="demo-sidebar__link <?= $activeSection === 'escape'   ? 'active' : '' ?>" href="/demo/escape">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Output Escaping
            </a>
            <a class="demo-sidebar__link <?= $activeSection === 'errors'   ? 'active' : '' ?>" href="/demo/errors">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Error Pages
            </a>
        </div>

    </aside>

    <!-- ── Main ─────────────────────────────────────────────────────────── -->
    <main class="demo-main">

        <!-- Breadcrumb -->
        <nav class="demo-breadcrumb" aria-label="breadcrumb">
            <a href="/">PHP-Zero</a>
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="/demo">Demo</a>
            <?php if (isset($breadcrumb)): ?>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
                <span><?= e($breadcrumb) ?></span>
            <?php endif; ?>
        </nav>

        <?= $content ?>

    </main>
</div>

</body>
</html>
