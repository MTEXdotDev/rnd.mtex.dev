<style>
    /* ── Hero ─────────────────────────────────────────────────────────── */
    .hero {
        text-align: center;
        padding: 4rem 1rem 3rem;
    }

    .hero__badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: rgba(56,189,248,.08);
        border: 1px solid rgba(56,189,248,.2);
        color: #38bdf8;
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .07em;
        text-transform: uppercase;
        padding: .35em .85em;
        border-radius: 999px;
        margin-bottom: 1.5rem;
    }

    .hero__badge svg { flex-shrink: 0; }

    .hero__title {
        font-size: clamp(2.5rem, 6vw, 4.5rem);
        font-weight: 800;
        letter-spacing: -2px;
        line-height: 1.1;
        color: #f1f5f9;
    }

    .hero__title-highlight {
        background: linear-gradient(135deg, #38bdf8, #818cf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero__desc {
        margin: 1.25rem auto 0;
        max-width: 540px;
        color: #94a3b8;
        font-size: 1.125rem;
        line-height: 1.7;
    }

    .hero__actions {
        margin-top: 2.5rem;
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    /* ── Feature grid ─────────────────────────────────────────────────── */
    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-top: 4rem;
    }

    .feature-card {
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: .75rem;
        padding: 1.5rem;
        transition: border-color .2s, transform .15s;
    }

    .feature-card:hover {
        border-color: #38bdf8;
        transform: translateY(-2px);
    }

    .feature-card__icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: .5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: .875rem;
    }

    .feature-card__icon--blue   { background: rgba(56,189,248,.12); color: #38bdf8; }
    .feature-card__icon--violet { background: rgba(129,140,248,.12); color: #818cf8; }
    .feature-card__icon--green  { background: rgba(34,197,94,.12);  color: #4ade80; }
    .feature-card__icon--amber  { background: rgba(251,191,36,.12); color: #fbbf24; }
    .feature-card__icon--rose   { background: rgba(251,113,133,.12); color: #fb7185; }
    .feature-card__icon--sky    { background: rgba(14,165,233,.12); color: #38bdf8; }

    .feature-card__title {
        font-size: 1rem;
        font-weight: 700;
        color: #f1f5f9;
        margin-bottom: .4rem;
    }

    .feature-card__desc {
        font-size: .875rem;
        color: #64748b;
        line-height: 1.65;
    }

    /* ── Code snippet ─────────────────────────────────────────────────── */
    .snippet {
        margin-top: 3rem;
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: .75rem;
        overflow: hidden;
    }

    .snippet__header {
        background: #0f172a;
        border-bottom: 1px solid #334155;
        padding: .75rem 1.25rem;
        display: flex;
        align-items: center;
        gap: .875rem;
    }

    .snippet__dots { display: flex; gap: .375rem; }

    .snippet__dot {
        width: .75rem;
        height: .75rem;
        border-radius: 50%;
    }

    .snippet__dot--red    { background: #ef4444; }
    .snippet__dot--yellow { background: #eab308; }
    .snippet__dot--green  { background: #22c55e; }

    .snippet__label {
        font-size: .75rem;
        color: #475569;
        font-family: 'JetBrains Mono', monospace;
        display: flex;
        align-items: center;
        gap: .375rem;
    }

    .snippet__body {
        padding: 1.5rem;
        overflow-x: auto;
        font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace;
        font-size: .8125rem;
        line-height: 1.8;
        color: #cbd5e1;
    }

    .t-kw  { color: #c084fc; }
    .t-fn  { color: #38bdf8; }
    .t-str { color: #4ade80; }
    .t-cmt { color: #475569; font-style: italic; }
    .t-cls { color: #fbbf24; }
    .t-num { color: #fb923c; }
</style>

<!-- ── Hero ──────────────────────────────────────────────────────────── -->
<section class="hero">
    <div class="hero__badge">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
        </svg>
        PHP 8.1+ &nbsp;·&nbsp; Zero Dependencies
    </div>

    <h1 class="hero__title">
        PHP<span class="hero__title-highlight">-Zero</span>
    </h1>

    <p class="hero__desc">
        <?= e($description ?? 'A lightweight micro-framework with zero external dependencies.') ?>
    </p>

    <div class="hero__actions">
        <a href="https://gh.mtex.dev/php-zero" class="btn btn-primary" target="_blank" rel="noopener">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61-.546-1.385-1.335-1.755-1.335-1.755-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 21.795 24 17.295 24 12c0-6.63-5.37-12-12-12"/>
            </svg>
            View on GitHub
        </a>
        <a href="/api/status" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            API Status
        </a>
    </div>
</section>

<!-- ── Feature grid ───────────────────────────────────────────────────── -->
<div class="features">

    <div class="feature-card">
        <div class="feature-card__icon feature-card__icon--blue">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
            </svg>
        </div>
        <h3 class="feature-card__title">Zero Dependencies</h3>
        <p class="feature-card__desc">No Composer packages. Pure PHP 8.1 — deploy anywhere without a vendor directory.</p>
    </div>

    <div class="feature-card">
        <div class="feature-card__icon feature-card__icon--violet">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/>
                <polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/>
            </svg>
        </div>
        <h3 class="feature-card__title">Regex Router</h3>
        <p class="feature-card__desc">GET, POST, PUT, DELETE with dynamic <code>{params}</code>, custom regex constraints, and JSON API groups.</p>
    </div>

    <div class="feature-card">
        <div class="feature-card__icon feature-card__icon--green">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
            </svg>
        </div>
        <h3 class="feature-card__title">Layout Engine</h3>
        <p class="feature-card__desc">Render views inside shared layouts. Pass data cleanly via <code>View::render()</code> or the fluent <code>view()</code> helper.</p>
    </div>

    <div class="feature-card">
        <div class="feature-card__icon feature-card__icon--amber">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>
        <h3 class="feature-card__title">Secure by Default</h3>
        <p class="feature-card__desc">PDO prepared statements, CSRF tokens, strict types, and header injection protection out of the box.</p>
    </div>

    <div class="feature-card">
        <div class="feature-card__icon feature-card__icon--rose">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <h3 class="feature-card__title">Smart Debugging</h3>
        <p class="feature-card__desc">Beautiful stack traces in dev mode. Clean HTTP error pages in production. <code>dd()</code> and <code>dump()</code> built in.</p>
    </div>

    <div class="feature-card">
        <div class="feature-card__icon feature-card__icon--sky">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
            </svg>
        </div>
        <h3 class="feature-card__title">Multi-Driver DB</h3>
        <p class="feature-card__desc">Switch between MySQL and SQLite with one <code>.env</code> variable. Same API, same query helpers.</p>
    </div>

</div>

<!-- ── Code snippet ───────────────────────────────────────────────────── -->
<div class="snippet">
    <div class="snippet__header">
        <div class="snippet__dots">
            <span class="snippet__dot snippet__dot--red"></span>
            <span class="snippet__dot snippet__dot--yellow"></span>
            <span class="snippet__dot snippet__dot--green"></span>
        </div>
        <span class="snippet__label">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
            </svg>
            routes/web.php
        </span>
    </div>
    <pre class="snippet__body"><span class="t-cmt">// Closure with a dynamic {name} parameter</span>
<span class="t-var">$router</span>-><span class="t-fn">get</span>(<span class="t-str">'/hello/{name}'</span>, <span class="t-kw">function</span>(<span class="t-kw">string</span> <span class="t-var">$name</span>): <span class="t-kw">string</span> {
    <span class="t-kw">return</span> <span class="t-cls">View</span>::<span class="t-fn">render</span>(<span class="t-str">'home'</span>, [<span class="t-str">'title'</span> => <span class="t-var">$name</span>], <span class="t-str">'app'</span>);
});

<span class="t-cmt">// Trigger an HTTP 404 from anywhere</span>
<span class="t-fn">abort</span>(<span class="t-num">404</span>, <span class="t-str">'Page not found'</span>);

<span class="t-cmt">// API group — automatic JSON headers</span>
<span class="t-var">$router</span>-><span class="t-fn">group</span>(<span class="t-str">'/api'</span>, <span class="t-kw">function</span>(<span class="t-cls">Router</span> <span class="t-var">$r</span>) {
    <span class="t-var">$r</span>-><span class="t-fn">get</span>(<span class="t-str">'/users'</span>, <span class="t-kw">fn</span>() => <span class="t-fn">json</span>([<span class="t-str">'users'</span> => []]));
}, <span class="t-kw">json</span>: <span class="t-kw">true</span>);</pre>
</div>
