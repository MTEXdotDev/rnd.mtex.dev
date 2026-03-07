<h1 class="demo-heading">Router &amp; Params</h1>
<p class="demo-subheading">
    PHP-Zero routes are compiled to regexes at dispatch time. Dynamic segments are captured
    as named groups and injected as handler arguments.
</p>

<!-- Static route -->
<div class="demo-section">
    <div class="demo-section__title">Static route</div>
    <div class="demo-panel">
        <div class="demo-panel__header">
            <span class="method method-get">GET</span>
            <span>/demo/routing</span>
        </div>
        <div class="demo-panel__body">
            <div class="code-block"><span class="t-var">$router</span>-><span class="t-fn">get</span>(<span class="t-str">'/demo/routing'</span>, <span class="t-str">'DemoController@routing'</span>);</div>
            <div class="result-box">
                <div class="result-box__label">Current route matched ✓</div>
                <div class="result-box__value"><?= e($currentUrl) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic {id} -->
<div class="demo-section">
    <div class="demo-section__title">Dynamic segment  <code>{id}</code></div>
    <div class="demo-panel">
        <div class="demo-panel__header">
            <span class="method method-get">GET</span>
            <span>/demo/user/{id}</span>
        </div>
        <div class="demo-panel__body">
            <div class="code-block"><span class="t-var">$router</span>-><span class="t-fn">get</span>(<span class="t-str">'/demo/user/{id}'</span>, <span class="t-str">'DemoController@user'</span>);</div>
            <p style="font-size:.875rem;color:#64748b;margin:.5rem 0 .75rem">
                Anything after <code>/demo/user/</code> is captured as <code>$id</code>.
                Try different values:
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem">
                <?php foreach ([1, 7, 42, 100, 999] as $n): ?>
                    <a href="/demo/user/<?= e($n) ?>" class="tag tag-blue" style="text-decoration:none">/demo/user/<?= e($n) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Regex slug -->
<div class="demo-section">
    <div class="demo-section__title">Regex constraint  <code>{slug:[a-z0-9-]+}</code></div>
    <div class="demo-panel">
        <div class="demo-panel__header">
            <span class="method method-get">GET</span>
            <span>/demo/slug/{slug:[a-z0-9-]+}</span>
        </div>
        <div class="demo-panel__body">
            <div class="code-block"><span class="t-var">$router</span>-><span class="t-fn">get</span>(<span class="t-str">'/demo/slug/{slug:[a-z0-9-]+}'</span>, <span class="t-str">'DemoController@slugRoute'</span>);</div>
            <p style="font-size:.875rem;color:#64748b;margin:.5rem 0 .75rem">
                Only lowercase letters, digits, and hyphens are allowed.
                An uppercase slug like <code>/demo/slug/Hello</code> will 404.
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:.5rem">
                <a href="/demo/slug/hello-world"         class="tag tag-green" style="text-decoration:none">/demo/slug/hello-world ✓</a>
                <a href="/demo/slug/php-zero-framework"  class="tag tag-green" style="text-decoration:none">/demo/slug/php-zero-framework ✓</a>
                <a href="/demo/slug/Hello-World"         class="tag tag-rose"  style="text-decoration:none">/demo/slug/Hello-World ✗ 404</a>
            </div>
        </div>
    </div>
</div>

<!-- Groups -->
<div class="demo-section">
    <div class="demo-section__title">Route groups</div>
    <div class="demo-panel">
        <div class="demo-panel__body">
            <div class="code-block"><span class="t-var">$router</span>-><span class="t-fn">group</span>(<span class="t-str">'/demo'</span>, <span class="t-kw">function</span>(<span class="t-cls">Router</span> <span class="t-var">$r</span>): <span class="t-kw">void</span> {
    <span class="t-var">$r</span>-><span class="t-fn">get</span>(<span class="t-str">'/'</span>,                           <span class="t-str">'DemoController@index'</span>);
    <span class="t-var">$r</span>-><span class="t-fn">get</span>(<span class="t-str">'/routing'</span>,                   <span class="t-str">'DemoController@routing'</span>);
    <span class="t-var">$r</span>-><span class="t-fn">get</span>(<span class="t-str">'/user/{id}'</span>,               <span class="t-str">'DemoController@user'</span>);
    <span class="t-var">$r</span>-><span class="t-fn">get</span>(<span class="t-str">'/slug/{slug:[a-z0-9-]+}'</span>,   <span class="t-str">'DemoController@slugRoute'</span>);
    <span class="t-var">$r</span>-><span class="t-fn">get</span>(<span class="t-str">'/query'</span>,                   <span class="t-str">'DemoController@query'</span>);
    <span class="t-var">$r</span>-><span class="t-fn">get</span>(<span class="t-str">'/forms'</span>,                   <span class="t-str">'DemoController@forms'</span>);
    <span class="t-var">$r</span>-><span class="t-fn">post</span>(<span class="t-str">'/forms'</span>,                  <span class="t-str">'DemoController@formsPost'</span>);
    <span class="t-var">$r</span>-><span class="t-fn">get</span>(<span class="t-str">'/trigger/{code:[0-9]{3}}'</span>, <span class="t-str">'DemoController@triggerError'</span>);
});</div>
        </div>
    </div>
</div>
