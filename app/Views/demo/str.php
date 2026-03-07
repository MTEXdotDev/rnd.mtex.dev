<h1 class="demo-heading">Str Utilities</h1>
<p class="demo-subheading">
    The <code>Str</code> class provides 20+ pure-static string helpers. All results below
    are computed live from the input strings defined in <code>DemoController::str()</code>.
</p>

<!-- Case transformations -->
<div class="demo-section">
    <div class="demo-section__title">Case transformations — input: <code><?= e($input) ?></code></div>
    <div class="str-grid">
        <?php foreach ($caseExamples as $call => $result): ?>
            <div class="str-row">
                <span class="str-row__call"><?= e($call) ?></span>
                <span class="str-row__result"><?= e($result) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Slug examples -->
<div class="demo-section">
    <div class="demo-section__title">Str::slug() — with transliteration</div>
    <div class="str-grid">
        <?php foreach ($slugExamples as $original => $slugged): ?>
            <div class="str-row">
                <span class="str-row__call"><?= e($original) ?></span>
                <span class="str-row__result"><?= e($slugged) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Truncation -->
<div class="demo-section">
    <div class="demo-section__title">Truncation</div>
    <div class="str-grid">
        <?php foreach ($truncateExamples as $call => $result): ?>
            <div class="str-row">
                <span class="str-row__call"><?= e($call) ?></span>
                <span class="str-row__result"><?= e($result) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Masking -->
<div class="demo-section">
    <div class="demo-section__title">Str::mask() — privacy redaction</div>
    <div class="str-grid">
        <?php foreach ($maskExamples as $call => $result): ?>
            <div class="str-row">
                <span class="str-row__call"><?= e($call) ?></span>
                <span class="str-row__result"><?= e($result) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Generation -->
<div class="demo-section">
    <div class="demo-section__title">Generation — new value on each page load</div>
    <div class="str-grid">
        <div class="str-row">
            <span class="str-row__call">Str::random(32)</span>
            <span class="str-row__result"><?= e($random32) ?></span>
        </div>
        <div class="str-row">
            <span class="str-row__call">Str::random(16)</span>
            <span class="str-row__result"><?= e($random16) ?></span>
        </div>
        <div class="str-row">
            <span class="str-row__call">Str::uuid()</span>
            <span class="str-row__result"><?= e($uuid1) ?></span>
        </div>
        <div class="str-row">
            <span class="str-row__call">Str::uuid()</span>
            <span class="str-row__result"><?= e($uuid2) ?></span>
        </div>
    </div>
</div>

<!-- Utilities -->
<div class="demo-section">
    <div class="demo-section__title">Utilities</div>
    <div class="str-grid">
        <?php foreach ($utilExamples as $call => $result): ?>
            <div class="str-row">
                <span class="str-row__call"><?= e($call) ?></span>
                <span class="str-row__result"><?= e(is_bool($result) ? ($result ? 'true' : 'false') : $result) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
