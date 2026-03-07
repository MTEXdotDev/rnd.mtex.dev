<h1 class="demo-heading">User <span style="color:#38bdf8">#<?= e($id) ?></span></h1>
<p class="demo-subheading">
    Route: <code>/demo/user/{id}</code> — the <code>{id}</code> segment was captured and
    passed directly as a typed <code>string</code> argument to the controller method.
</p>

<div class="demo-section">
    <div class="demo-section__title">Route match</div>
    <div class="demo-panel">
        <div class="demo-panel__header">
            <span class="method method-get">GET</span>
            <span>/demo/user/<?= e($id) ?></span>
        </div>
        <div class="demo-panel__body">
            <table class="kv-table">
                <tr><td>Route pattern</td>   <td>/demo/user/{id}</td></tr>
                <tr><td>Captured $id</td>    <td><?= e($id) ?></td></tr>
                <tr><td>is_numeric</td>      <td><?= e($isNumeric ? 'true' : 'false') ?></td></tr>
                <tr><td>Controller</td>      <td>DemoController@user</td></tr>
                <tr><td>Request method</td>  <td><?= e($method) ?></td></tr>
            </table>
        </div>
    </div>
</div>

<div class="demo-section">
    <div class="demo-section__title">Controller code</div>
    <div class="code-block"><span class="t-kw">public function</span> <span class="t-fn">user</span>(<span class="t-kw">string</span> <span class="t-var">$id</span>): <span class="t-kw">string</span>
{
    <span class="t-cmt">// Validate that $id is numeric — abort with 404 if not</span>
    <span class="t-kw">if</span> (!<span class="t-fn">is_numeric</span>(<span class="t-var">$id</span>)) {
        <span class="t-fn">abort</span>(<span class="t-num">404</span>, <span class="t-str">"User ID must be numeric."</span>);
    }

    <span class="t-kw">return</span> <span class="t-cls">View</span>::<span class="t-fn">render</span>(<span class="t-str">'demo/user'</span>, [
        <span class="t-str">'id'</span>        => <span class="t-var">$id</span>,
        <span class="t-str">'isNumeric'</span> => <span class="t-fn">is_numeric</span>(<span class="t-var">$id</span>),
        <span class="t-str">'method'</span>    => <span class="t-cls">Request</span>::<span class="t-fn">method</span>(),
    ], <span class="t-str">'demo'</span>);
}</div>
</div>

<div class="demo-section">
    <div class="demo-section__title">Try other IDs</div>
    <div style="display:flex;flex-wrap:wrap;gap:.5rem">
        <?php foreach ([1, 7, 42, 100, 999, 'abc'] as $n): ?>
            <a href="/demo/user/<?= e($n) ?>"
               class="tag <?= $n === $id ? 'tag-blue' : 'tag-muted' ?>"
               style="text-decoration:none">
                /demo/user/<?= e($n) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
