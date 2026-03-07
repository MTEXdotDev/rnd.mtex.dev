<h1 class="demo-heading">Output Escaping</h1>
<p class="demo-subheading">
    PHP-Zero ships four context-aware escaping helpers. Using the wrong one (or none) is the
    root cause of XSS. Each section below shows the attack string, the safe output, and the
    underlying call.
</p>

<?php
// Attack strings used throughout this demo
$xss       = '<script>alert("XSS")</script>';
$attrXss   = '" onmouseover="alert(1)" x="';
$jsValue   = ['user' => '<Alice>', 'role' => "admin' OR '1'='1"];
$trusted   = '<strong>This was rendered with <code>raw()</code></strong>';
?>

<!-- e() -->
<div class="demo-section">
    <div class="demo-section__title">e() — HTML text content</div>
    <div class="demo-panel">
        <div class="demo-panel__body">
            <table class="kv-table">
                <tr>
                    <td>Attack input</td>
                    <td><code><?= e($xss) ?></code></td>
                </tr>
                <tr>
                    <td>Rendered safely</td>
                    <td><?= e($xss) ?></td>
                </tr>
                <tr>
                    <td>Source HTML</td>
                    <td><code><?= e('<?= e($xss) ?>') ?></code></td>
                </tr>
                <tr>
                    <td>null / false</td>
                    <td><code>"<?= e(null) ?>"</code> / <code>"<?= e(false) ?>"</code> — safely empty strings</td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- attr() -->
<div class="demo-section">
    <div class="demo-section__title">attr() — HTML attributes</div>
    <div class="demo-panel">
        <div class="demo-panel__body">
            <table class="kv-table">
                <tr>
                    <td>Attack input</td>
                    <td><code><?= e($attrXss) ?></code></td>
                </tr>
                <tr>
                    <td>Rendered safely</td>
                    <td>
                        <input type="text" value="<?= attr($attrXss) ?>"
                               style="background:#0a0f1e;border:1px solid #334155;color:#e2e8f0;padding:.25em .5em;border-radius:.25rem;font-size:.75rem;width:100%"
                               readonly>
                    </td>
                </tr>
                <tr>
                    <td>Source HTML</td>
                    <td><code><?= e('<input value="<?= attr($attrXss) ?>">') ?></code></td>
                </tr>
                <tr>
                    <td>Select with attr()</td>
                    <td>
                        <?php $opts = ['admin', 'editor', 'viewer']; $selected = 'editor'; ?>
                        <select style="background:#0a0f1e;border:1px solid #334155;color:#e2e8f0;padding:.25em .5em;border-radius:.25rem;font-size:.75rem">
                            <?php foreach ($opts as $opt): ?>
                                <option value="<?= attr($opt) ?>" <?= $opt === $selected ? 'selected' : '' ?>>
                                    <?= e(ucfirst($opt)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- raw() -->
<div class="demo-section">
    <div class="demo-section__title">raw() — trusted HTML opt-out</div>
    <div class="demo-panel">
        <div class="demo-panel__body">
            <table class="kv-table">
                <tr>
                    <td>Trusted HTML string</td>
                    <td><code><?= e($trusted) ?></code></td>
                </tr>
                <tr>
                    <td>Rendered with raw()</td>
                    <td><?= raw($trusted) ?></td>
                </tr>
                <tr>
                    <td>Same string with e()</td>
                    <td><?= e($trusted) ?></td>
                </tr>
            </table>
            <div style="margin-top:.875rem;padding:.75rem;background:rgba(251,113,133,.06);border:1px solid rgba(251,113,133,.2);border-radius:.375rem;font-size:.8125rem;color:#f87171">
                <strong>Warning:</strong> Never pass user input to <code>raw()</code>. It is
                intended for pre-sanitised content such as a Markdown renderer's output.
            </div>
        </div>
    </div>
</div>

<!-- js() -->
<div class="demo-section">
    <div class="demo-section__title">js() — inline script values</div>
    <div class="demo-panel">
        <div class="demo-panel__body">
            <div class="code-block"><span class="t-cmt">// PHP</span>
<span class="t-var">$config</span> = <?= e(var_export($jsValue, true)) ?>;
</div>
            <div class="code-block"><span class="t-cmt">// In your view template</span>
<span class="t-kw">&lt;script&gt;</span>
    const config = <span class="t-fn">&lt;?= js($config) ?&gt;</span>;
<span class="t-kw">&lt;/script&gt;</span>

<span class="t-cmt">// Rendered output (safe — &lt;/script&gt; injection impossible)</span>
const config = <?= js($jsValue) ?>;
</div>
            <script>
                // This actually runs — open console to verify
                const demoConfig = <?= js($jsValue) ?>;
                console.log('[PHP-Zero demo] js() output:', demoConfig);
            </script>
            <div style="margin-top:.75rem;font-size:.8125rem;color:#64748b">
                Open your browser's developer console to see the safely-rendered value.
            </div>
        </div>
    </div>
</div>

<!-- Summary -->
<div class="demo-section">
    <div class="demo-section__title">Quick reference</div>
    <div class="code-block"><span class="t-cmt">// e()    → HTML body text (use this by default)</span>
<span class="t-kw">&lt;?=</span> <span class="t-fn">e</span>(<span class="t-var">$user</span>[<span class="t-str">'name'</span>]) <span class="t-kw">?&gt;</span>

<span class="t-cmt">// attr() → HTML attribute values</span>
<span class="t-kw">&lt;input</span> value=<span class="t-str">"&lt;?= attr($value) ?&gt;"</span><span class="t-kw">&gt;</span>

<span class="t-cmt">// raw()  → trusted/pre-sanitised HTML</span>
<span class="t-kw">&lt;?=</span> <span class="t-fn">raw</span>(<span class="t-var">$markdownHtml</span>) <span class="t-kw">?&gt;</span>

<span class="t-cmt">// js()   → inline &lt;script&gt; values</span>
<span class="t-kw">&lt;script&gt;</span>const data = <span class="t-kw">&lt;?=</span> <span class="t-fn">js</span>(<span class="t-var">$data</span>) <span class="t-kw">?&gt;</span>;<span class="t-kw">&lt;/script&gt;</span></div>
</div>
