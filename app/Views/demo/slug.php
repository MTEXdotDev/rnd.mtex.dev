<h1 class="demo-heading">Slug Route</h1>
<p class="demo-subheading">
    Pattern: <code>/demo/slug/{slug:[a-z0-9-]+}</code> — the inline regex constraint
    <code>[a-z0-9-]+</code> means only lowercase-alphanumeric-with-hyphens strings match.
    Anything else falls through to a 404.
</p>

<div class="demo-section">
    <div class="demo-section__title">Matched segment</div>
    <div class="demo-panel">
        <div class="demo-panel__header">
            <span class="method method-get">GET</span>
            <span>/demo/slug/<?= e($slug) ?></span>
        </div>
        <div class="demo-panel__body">
            <table class="kv-table">
                <tr><td>Captured $slug</td>     <td><?= e($slug) ?></td></tr>
                <tr><td>Length</td>             <td><?= e($length) ?> chars</td></tr>
                <tr><td>Word count</td>         <td><?= e($wordCount) ?> words</td></tr>
                <tr><td>Studly version</td>     <td><?= e($studly) ?></td></tr>
                <tr><td>Title version</td>      <td><?= e($titleCase) ?></td></tr>
            </table>
        </div>
    </div>
</div>

<div class="demo-section">
    <div class="demo-section__title">Str::slug() conversion</div>
    <div class="demo-panel">
        <div class="demo-panel__body">
            <p style="font-size:.875rem;color:#64748b;margin-bottom:.75rem">
                Arbitrary strings slugified on the fly:
            </p>
            <?php foreach ($slugExamples as $original => $slugged): ?>
                <div style="display:flex;align-items:center;gap:.75rem;padding:.4rem 0;border-bottom:1px solid #1e293b;font-size:.8125rem">
                    <span style="color:#64748b;font-family:monospace;flex:1"><?= e($original) ?></span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#334155" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    <span style="color:#4ade80;font-family:monospace;flex:1"><?= e($slugged) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="demo-section">
    <div class="demo-section__title">Try other slugs</div>
    <div style="display:flex;flex-wrap:wrap;gap:.5rem">
        <?php foreach (['hello-world', 'php-zero-framework', 'my-blog-post-2025', 'a', 'Hello-World'] as $s): ?>
            <?php $valid = preg_match('/^[a-z0-9-]+$/', $s); ?>
            <a href="/demo/slug/<?= e($s) ?>"
               class="tag <?= $valid ? 'tag-green' : 'tag-rose' ?>"
               style="text-decoration:none">
                <?= e($s) ?> <?= $valid ? '✓' : '✗' ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
