<h1 class="demo-heading">Database — SQLite</h1>
<p class="demo-subheading">
    Live CRUD against a SQLite database (<code>database/database.sqlite</code>). The
    <code>demo_notes</code> table is auto-created on first visit. Data actually persists.
</p>

<?php if ($flash = \App\Core\Session::getFlash('db_success')): ?>
    <div class="alert alert-success" style="margin-bottom:1.5rem">
        <div style="display:flex;align-items:center;gap:.5rem">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <?= e($flash) ?>
        </div>
    </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem">

    <!-- Add note -->
    <div class="demo-section" style="margin:0">
        <div class="demo-section__title">INSERT a note</div>
        <div class="demo-panel">
            <div class="demo-panel__header">
                <span class="method method-post">POST</span>
                <span>/demo/database</span>
            </div>
            <div class="demo-panel__body">
                <form method="POST" action="/demo/database" style="display:flex;flex-direction:column;gap:.75rem">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="add">
                    <div>
                        <input class="form-input" type="text" name="note"
                               placeholder="Type a note…" maxlength="200" required>
                        <?php if ($e = errors('note')): ?>
                            <div class="field-error"><?= e($e) ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary" style="align-self:flex-start">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add note
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="demo-section" style="margin:0">
        <div class="demo-section__title">Database stats</div>
        <div class="demo-panel" style="height:100%">
            <div class="demo-panel__body">
                <table class="kv-table">
                    <tr><td>Driver</td>       <td><span class="tag tag-blue"><?= e($driver) ?></span></td></tr>
                    <tr><td>Table</td>        <td><code>demo_notes</code></td></tr>
                    <tr><td>Row count</td>    <td><?= e($count) ?></td></tr>
                    <tr><td>Last insert</td>  <td><?= e($lastInsertId ?? '—') ?></td></tr>
                    <tr><td>PHP-Zero DB</td>  <td><code>Database::getInstance()</code></td></tr>
                </table>
                <form method="POST" action="/demo/database" style="margin-top:1rem">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="flush">
                    <button type="submit" class="btn btn-secondary" style="font-size:.8125rem;padding:.5em 1em">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                        Flush all rows
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Notes list -->
<div class="demo-section">
    <div class="demo-section__title">All notes (SELECT *)</div>
    <div class="demo-panel">
        <div class="demo-panel__header">
            <span class="method method-get">SQL</span>
            <span>SELECT id, body, created_at FROM demo_notes ORDER BY id DESC</span>
        </div>
        <div class="demo-panel__body">
            <?php if (empty($notes)): ?>
                <p style="color:#475569;font-size:.875rem">No notes yet — add one above.</p>
            <?php else: ?>
                <table class="kv-table" style="width:100%">
                    <thead>
                        <tr style="border-bottom:1px solid #334155">
                            <td style="color:#38bdf8;width:3rem">id</td>
                            <td style="color:#38bdf8">body</td>
                            <td style="color:#38bdf8;white-space:nowrap">created_at</td>
                            <td style="color:#38bdf8;width:4rem"></td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($notes as $note): ?>
                        <tr>
                            <td><?= e($note['id']) ?></td>
                            <td><?= e($note['body']) ?></td>
                            <td style="color:#475569;white-space:nowrap"><?= e($note['created_at']) ?></td>
                            <td>
                                <form method="POST" action="/demo/database" style="margin:0">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_action" value="delete">
                                    <input type="hidden" name="id" value="<?= attr($note['id']) ?>">
                                    <button type="submit" class="tag tag-rose" style="background:none;border:none;cursor:pointer;font-family:monospace">
                                        del
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="demo-section">
    <div class="demo-section__title">Controller code</div>
    <div class="code-block"><span class="t-var">$db</span> = <span class="t-cls">Database</span>::<span class="t-fn">getInstance</span>(); <span class="t-cmt">// SQLite (DB_DRIVER=sqlite)</span>

<span class="t-cmt">// Auto-create table on first use</span>
<span class="t-kw">if</span> (!<span class="t-var">$db</span>-><span class="t-fn">tableExists</span>(<span class="t-str">'demo_notes'</span>)) {
    <span class="t-var">$db</span>-><span class="t-fn">statement</span>(<span class="t-str">'CREATE TABLE demo_notes (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        body       TEXT NOT NULL,
        created_at TEXT NOT NULL
    )'</span>);
}

<span class="t-cmt">// INSERT</span>
<span class="t-var">$id</span> = <span class="t-var">$db</span>-><span class="t-fn">insert</span>(<span class="t-str">'demo_notes'</span>, [
    <span class="t-str">'body'</span>       => <span class="t-var">$note</span>,
    <span class="t-str">'created_at'</span> => <span class="t-fn">date</span>(<span class="t-str">'Y-m-d H:i:s'</span>),
]);

<span class="t-cmt">// SELECT all</span>
<span class="t-var">$notes</span> = <span class="t-var">$db</span>-><span class="t-fn">select</span>(<span class="t-str">'SELECT * FROM demo_notes ORDER BY id DESC'</span>);

<span class="t-cmt">// DELETE by id</span>
<span class="t-var">$db</span>-><span class="t-fn">delete</span>(<span class="t-str">'demo_notes'</span>, [<span class="t-str">'id'</span> => <span class="t-var">$id</span>]);</div>
</div>
