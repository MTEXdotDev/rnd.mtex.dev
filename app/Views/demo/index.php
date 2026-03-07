<h1 class="demo-heading">Framework Demo</h1>
<p class="demo-subheading">
    Interactive examples for every PHP-Zero feature. Each section shows real running code —
    click any card to explore, or use the sidebar.
</p>

<div class="demo-nav-grid">

    <a class="demo-nav-card" href="/demo/routing">
        <div class="demo-nav-card__icon" style="background:rgba(56,189,248,.1);color:#38bdf8">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/></svg>
        </div>
        <div class="demo-nav-card__title">Router &amp; Params</div>
        <div class="demo-nav-card__desc">Dynamic segments, regex constraints, GET/POST groups</div>
    </a>

    <a class="demo-nav-card" href="/demo/query?search=php-zero&sort=name&page=2">
        <div class="demo-nav-card__icon" style="background:rgba(129,140,248,.1);color:#818cf8">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <div class="demo-nav-card__title">Query Strings</div>
        <div class="demo-nav-card__desc">Request::get(), has(), all() — typed input reading</div>
    </a>

    <a class="demo-nav-card" href="/demo/forms">
        <div class="demo-nav-card__icon" style="background:rgba(251,191,36,.1);color:#fbbf24">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="12" y2="17"/></svg>
        </div>
        <div class="demo-nav-card__title">Forms + Validation</div>
        <div class="demo-nav-card__desc">Live POST form, Validator rules, error display, old()</div>
    </a>

    <a class="demo-nav-card" href="/demo/database">
        <div class="demo-nav-card__icon" style="background:rgba(34,197,94,.1);color:#4ade80">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/></svg>
        </div>
        <div class="demo-nav-card__title">Database</div>
        <div class="demo-nav-card__desc">SQLite CRUD — insert, select, update, delete live</div>
    </a>

    <a class="demo-nav-card" href="/demo/cache">
        <div class="demo-nav-card__icon" style="background:rgba(14,165,233,.1);color:#38bdf8">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        </div>
        <div class="demo-nav-card__title">Cache</div>
        <div class="demo-nav-card__desc">File cache with TTL, remember(), flush — live counters</div>
    </a>

    <a class="demo-nav-card" href="/demo/str">
        <div class="demo-nav-card__icon" style="background:rgba(251,113,133,.1);color:#fb7185">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
        </div>
        <div class="demo-nav-card__title">Str Utilities</div>
        <div class="demo-nav-card__desc">slug, truncate, mask, random, uuid and 15+ more</div>
    </a>

    <a class="demo-nav-card" href="/demo/escape">
        <div class="demo-nav-card__icon" style="background:rgba(129,140,248,.1);color:#818cf8">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div class="demo-nav-card__title">Output Escaping</div>
        <div class="demo-nav-card__desc">e(), attr(), raw(), js() — XSS protection in context</div>
    </a>

    <a class="demo-nav-card" href="/demo/errors">
        <div class="demo-nav-card__icon" style="background:rgba(251,113,133,.1);color:#fb7185">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="demo-nav-card__title">Error Pages</div>
        <div class="demo-nav-card__desc">abort() triggers, HTTP status codes, JSON errors</div>
    </a>

</div>

<div style="margin-top:2.5rem;padding:1.25rem;background:#0a0f1e;border:1px solid #1e293b;border-radius:.625rem">
    <div style="font-size:.6875rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#334155;margin-bottom:.75rem">Quick links</div>
    <div style="display:flex;flex-wrap:wrap;gap:.5rem">
        <a href="/demo/user/1"     style="font-size:.75rem;font-family:monospace;color:#64748b;text-decoration:none;background:#131c2e;border:1px solid #1e293b;padding:.25em .6em;border-radius:.3rem">/demo/user/1</a>
        <a href="/demo/user/42"    style="font-size:.75rem;font-family:monospace;color:#64748b;text-decoration:none;background:#131c2e;border:1px solid #1e293b;padding:.25em .6em;border-radius:.3rem">/demo/user/42</a>
        <a href="/demo/slug/hello-world" style="font-size:.75rem;font-family:monospace;color:#64748b;text-decoration:none;background:#131c2e;border:1px solid #1e293b;padding:.25em .6em;border-radius:.3rem">/demo/slug/hello-world</a>
        <a href="/demo/slug/php-zero-framework" style="font-size:.75rem;font-family:monospace;color:#64748b;text-decoration:none;background:#131c2e;border:1px solid #1e293b;padding:.25em .6em;border-radius:.3rem">/demo/slug/php-zero-framework</a>
        <a href="/demo/query?search=hello&sort=date&page=3" style="font-size:.75rem;font-family:monospace;color:#64748b;text-decoration:none;background:#131c2e;border:1px solid #1e293b;padding:.25em .6em;border-radius:.3rem">/demo/query?search=hello&amp;sort=date&amp;page=3</a>
        <a href="/demo/trigger/404" style="font-size:.75rem;font-family:monospace;color:#64748b;text-decoration:none;background:#131c2e;border:1px solid #1e293b;padding:.25em .6em;border-radius:.3rem">/demo/trigger/404</a>
        <a href="/demo/trigger/403" style="font-size:.75rem;font-family:monospace;color:#64748b;text-decoration:none;background:#131c2e;border:1px solid #1e293b;padding:.25em .6em;border-radius:.3rem">/demo/trigger/403</a>
    </div>
</div>
