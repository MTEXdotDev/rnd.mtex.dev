<h1 class="demo-heading">Query Strings</h1>
<p class="demo-subheading">
    <code>Request::get()</code> reads from <code>$_GET</code> with type-safe fallbacks.
    This page was loaded with: <code style="color:#38bdf8"><?= e($currentQuery ?: '(no query string)') ?></code>
</p>

<div class="demo-section">
    <div class="demo-section__title">Parsed parameters</div>
    <div class="demo-panel">
        <div class="demo-panel__header">
            <span class="method method-get">GET</span>
            <span>/demo/query<?= e($currentQuery ? '?' . $currentQuery : '') ?></span>
        </div>
        <div class="demo-panel__body">
            <?php if (empty($params)): ?>
                <p style="color:#475569;font-size:.875rem">No query parameters found. Try adding some — see examples below.</p>
            <?php else: ?>
                <table class="kv-table">
                    <?php foreach ($params as $key => $value): ?>
                        <tr>
                            <td><?= e($key) ?></td>
                            <td>
                                <?= e($value) ?>
                                <span class="tag tag-muted" style="margin-left:.375rem"><?= e(gettype($value)) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="demo-section">
    <div class="demo-section__title">Request class reads</div>
    <div class="code-block"><span class="t-cmt">// Read individual keys with typed fallbacks</span>
<span class="t-var">$search</span> = <span class="t-cls">Request</span>::<span class="t-fn">get</span>(<span class="t-str">'search'</span>, <span class="t-str">''</span>);        <span class="t-cmt">// <?= e($search ?: '(empty)') ?></span>
<span class="t-var">$sort</span>   = <span class="t-cls">Request</span>::<span class="t-fn">get</span>(<span class="t-str">'sort'</span>, <span class="t-str">'name'</span>);      <span class="t-cmt">// <?= e($sort) ?></span>
<span class="t-var">$page</span>   = (int) <span class="t-cls">Request</span>::<span class="t-fn">get</span>(<span class="t-str">'page'</span>, <span class="t-str">'1'</span>);  <span class="t-cmt">// <?= e($page) ?></span>

<span class="t-cmt">// Existence check</span>
<span class="t-cls">Request</span>::<span class="t-fn">has</span>(<span class="t-str">'search'</span>); <span class="t-cmt">// <?= e($hasSearch ? 'true' : 'false') ?></span>

<span class="t-cmt">// All GET params as array</span>
<span class="t-var">$all</span> = <span class="t-cls">Request</span>::<span class="t-fn">all</span>(); <span class="t-cmt">// <?= e(json_encode($params)) ?></span></div>
</div>

<div class="demo-section">
    <div class="demo-section__title">Try these URLs</div>
    <div style="display:flex;flex-direction:column;gap:.375rem">
        <?php foreach ($examples as $url => $desc): ?>
            <a href="<?= e($url) ?>" style="font-size:.8125rem;text-decoration:none;display:flex;align-items:center;gap:.75rem;padding:.4rem .75rem;background:#0a0f1e;border:1px solid #1e293b;border-radius:.375rem;transition:border-color .15s"
               onmouseover="this.style.borderColor='#334155'" onmouseout="this.style.borderColor='#1e293b'">
                <code style="color:#38bdf8;flex:1"><?= e($url) ?></code>
                <span style="color:#475569"><?= e($desc) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
