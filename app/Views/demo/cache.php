<h1 class="demo-heading">Cache</h1>
<p class="demo-subheading">
    File-based key/value cache stored in <code>storage/cache/</code>. The counter below
    is cached for <strong>30 seconds</strong> — it only recomputes on a cold miss.
</p>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem">

    <!-- Live counter -->
    <div class="demo-section" style="margin:0">
        <div class="demo-section__title">remember() — cached counter</div>
        <div class="demo-panel">
            <div class="demo-panel__body" style="text-align:center;padding:2rem">
                <div style="font-size:3.5rem;font-weight:800;color:#38bdf8;letter-spacing:-2px;line-height:1">
                    <?= e($counter) ?>
                </div>
                <div style="font-size:.75rem;color:#475569;margin-top:.5rem">
                    requests since epoch (cached)
                </div>
                <div style="margin-top:1rem;display:flex;align-items:center;justify-content:center;gap:.5rem">
                    <?php if ($isHit): ?>
                        <span class="tag tag-green">Cache HIT</span>
                    <?php else: ?>
                        <span class="tag tag-amber">Cache MISS</span>
                    <?php endif; ?>
                    <span style="font-size:.75rem;color:#475569">TTL: <?= e($ttlRemaining) ?>s remaining</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <div class="demo-section" style="margin:0">
        <div class="demo-section__title">Controls</div>
        <div class="demo-panel" style="height:100%">
            <div class="demo-panel__body" style="display:flex;flex-direction:column;gap:.75rem">
                <form method="POST" action="/demo/cache">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="bust">
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                        Bust counter cache
                    </button>
                </form>
                <form method="POST" action="/demo/cache">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="flush">
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        Flush all cache
                    </button>
                </form>
                <a href="/demo/cache" class="btn btn-secondary" style="width:100%;justify-content:center;text-decoration:none">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4v6h6"/><path d="M23 20v-6h-6"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>
                    Reload page
                </a>
            </div>
        </div>
    </div>

</div>

<!-- Stored keys -->
<div class="demo-section">
    <div class="demo-section__title">Cache entries on disk</div>
    <div class="demo-panel">
        <div class="demo-panel__body">
            <?php if (empty($keys)): ?>
                <p style="color:#475569;font-size:.875rem">No cache files found in <code>storage/cache/</code>.</p>
            <?php else: ?>
                <table class="kv-table">
                    <thead>
                        <tr style="border-bottom:1px solid #334155">
                            <td style="color:#38bdf8">Key</td>
                            <td style="color:#38bdf8">TTL remaining</td>
                            <td style="color:#38bdf8">Size</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($keys as $entry): ?>
                            <tr>
                                <td><?= e($entry['key']) ?></td>
                                <td>
                                    <?php if ($entry['ttl'] === 0): ?>
                                        <span class="tag tag-violet">forever</span>
                                    <?php elseif ($entry['ttl'] > 0): ?>
                                        <span class="tag tag-green"><?= e($entry['ttl']) ?>s</span>
                                    <?php else: ?>
                                        <span class="tag tag-rose">expired</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color:#475569"><?= e($entry['size']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="demo-section">
    <div class="demo-section__title">Code used on this page</div>
    <div class="code-block"><span class="t-cmt">// remember() — compute once, cache for 30s</span>
<span class="t-var">$counter</span> = <span class="t-cls">Cache</span>::<span class="t-fn">remember</span>(<span class="t-str">'demo_counter'</span>, <span class="t-num">30</span>, function (): <span class="t-kw">int</span> {
    <span class="t-kw">return</span> <span class="t-fn">random_int</span>(<span class="t-num">1_000</span>, <span class="t-num">999_999</span>);
});

<span class="t-var">$isHit</span>        = <span class="t-cls">Cache</span>::<span class="t-fn">has</span>(<span class="t-str">'demo_counter'</span>);
<span class="t-var">$ttlRemaining</span> = <span class="t-cls">Cache</span>::<span class="t-fn">ttl</span>(<span class="t-str">'demo_counter'</span>);

<span class="t-cmt">// Bust a single key</span>
<span class="t-cls">Cache</span>::<span class="t-fn">forget</span>(<span class="t-str">'demo_counter'</span>);

<span class="t-cmt">// Flush everything</span>
<span class="t-cls">Cache</span>::<span class="t-fn">flush</span>();</div>
</div>
