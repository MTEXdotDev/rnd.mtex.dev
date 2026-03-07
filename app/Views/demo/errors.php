<h1 class="demo-heading">Error Pages</h1>
<p class="demo-subheading">
    <code>abort($code, $message)</code> throws an <code>HttpException</code> caught by
    <code>ExceptionHandler</code>, which renders a styled page with the correct HTTP status.
    API requests always receive JSON regardless of <code>APP_DEBUG</code>.
</p>

<div class="demo-section">
    <div class="demo-section__title">Trigger HTTP errors</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.75rem">
        <?php foreach ($errorCodes as $code => $info): ?>
            <a href="/demo/trigger/<?= e($code) ?>"
               class="demo-nav-card"
               style="text-decoration:none">
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                    <span class="tag <?= $info['tag'] ?>" style="font-size:.875rem;font-weight:800"><?= e($code) ?></span>
                </div>
                <div class="demo-nav-card__title" style="font-size:.875rem"><?= e($info['label']) ?></div>
                <div class="demo-nav-card__desc"><?= e($info['desc']) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="demo-section">
    <div class="demo-section__title">How it works</div>
    <div class="code-block"><span class="t-cmt">// Anywhere in a controller or route closure:</span>
<span class="t-fn">abort</span>(<span class="t-num">404</span>);                             <span class="t-cmt">// uses default message "Not Found"</span>
<span class="t-fn">abort</span>(<span class="t-num">403</span>, <span class="t-str">'You cannot access this page.'</span>);
<span class="t-fn">abort</span>(<span class="t-num">401</span>, <span class="t-str">'Please log in first.'</span>);
<span class="t-fn">abort</span>(<span class="t-num">422</span>, <span class="t-str">'Validation failed.'</span>);

<span class="t-cmt">// Factory shortcuts on HttpException:</span>
<span class="t-kw">throw</span> <span class="t-cls">HttpException</span>::<span class="t-fn">notFound</span>();
<span class="t-kw">throw</span> <span class="t-cls">HttpException</span>::<span class="t-fn">forbidden</span>(<span class="t-str">'Admins only.'</span>);
<span class="t-kw">throw</span> <span class="t-cls">HttpException</span>::<span class="t-fn">unauthorized</span>();</div>
</div>

<div class="demo-section">
    <div class="demo-section__title">JSON errors (API requests)</div>
    <div class="demo-panel">
        <div class="demo-panel__header">
            <span class="method method-get">GET</span>
            <span>/demo/trigger/404  <span style="color:#334155;margin-left:.5rem">Accept: application/json</span></span>
        </div>
        <div class="demo-panel__body">
            <div class="code-block">{
    <span class="t-key">"error"</span>: <span class="t-str">"Not Found"</span>,
    <span class="t-key">"code"</span>:  <span class="t-num">404</span>
}</div>
            <p style="font-size:.8125rem;color:#475569;margin-top:.75rem">
                Routes under <code>/api/</code> or any request with
                <code>Accept: application/json</code> automatically receive a JSON error body.
                Stack traces are included only when <code>APP_DEBUG=true</code>.
            </p>
        </div>
    </div>
</div>

<div class="demo-section">
    <div class="demo-section__title">APP_DEBUG mode</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="demo-panel">
            <div class="demo-panel__header" style="gap:.5rem">
                <span class="tag tag-green">APP_DEBUG=true</span>
            </div>
            <div class="demo-panel__body">
                <ul style="font-size:.8125rem;color:#64748b;padding-left:1.25rem;line-height:2">
                    <li>Full stack trace rendered</li>
                    <li>Source file context highlighted</li>
                    <li>Exception class name shown</li>
                    <li>JSON: includes message + trace</li>
                </ul>
            </div>
        </div>
        <div class="demo-panel">
            <div class="demo-panel__header" style="gap:.5rem">
                <span class="tag tag-rose">APP_DEBUG=false</span>
            </div>
            <div class="demo-panel__body">
                <ul style="font-size:.8125rem;color:#64748b;padding-left:1.25rem;line-height:2">
                    <li>Generic 500 page shown</li>
                    <li>No internals exposed</li>
                    <li>HttpException shows its message</li>
                    <li>JSON: error + code only</li>
                </ul>
            </div>
        </div>
    </div>
</div>
