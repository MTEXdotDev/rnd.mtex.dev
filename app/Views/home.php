<!-- Nav -->
<nav>
    <a href="/" class="nav-logo">
        <span class="dot"></span>
        rnd<span style="color:var(--muted);">.mtex.dev</span>
    </a>
    <div class="nav-links">
        <a href="#endpoints">Endpoints</a>
        <a href="#examples">Examples</a>
        <a href="/api/ping">Ping</a>
        <a href="https://mtex.dev" target="_blank">MTEX.dev ↗</a>
    </div>
</nav>

<!-- Hero -->
<div class="hero">
    <div class="hero-badge">
        <span>⚡</span>
        <span>Zero auth · Zero setup · Pure GET</span>
    </div>
    <h1>
        Random data,<br>
        <span class="grad">on demand.</span>
    </h1>
    <p>
        Generate UUIDs, names, colors, lorem ipsum, dice rolls, passwords,
        and more — straight from a GET request. Built for developers.
    </p>
    <div class="hero-actions">
        <a href="#endpoints" class="btn btn-primary">↓ Browse endpoints</a>
        <a href="/api/endpoints" class="btn btn-ghost">View JSON spec</a>
    </div>

    <!-- Live demo ticker -->
    <div class="ticker" style="margin-top: 3rem;">
        <div class="ticker-bar">
            <span class="ticker-dot"></span>
            live requests
        </div>
        <div class="ticker-body" id="ticker">
            <!-- Populated by JS -->
        </div>
    </div>
</div>

<!-- Stats -->
<div class="stats">
    <div class="stats-inner">
        <div class="stat"><div class="stat-num">17</div><div class="stat-lbl">endpoints</div></div>
        <div class="stat"><div class="stat-num">0</div><div class="stat-lbl">auth required</div></div>
        <div class="stat"><div class="stat-num">∞</div><div class="stat-lbl">requests/day</div></div>
        <div class="stat"><div class="stat-num">PHP</div><div class="stat-lbl">powered by php-zero</div></div>
    </div>
</div>

<!-- Endpoints -->
<section id="endpoints">
    <div class="section-title">// endpoints</div>
    <div class="endpoints-grid">
        <?php
        $endpoints = [
            ['/api/uuid',     'UUID v4',              ['count']],
            ['/api/name',     'Person name',           ['count', 'gender']],
            ['/api/email',    'Email address',         ['count', 'domain']],
            ['/api/color',    'Color value',           ['count', 'format']],
            ['/api/gradient', 'CSS gradient',          ['count', 'type']],
            ['/api/number',   'Random number',         ['min', 'max', 'float']],
            ['/api/string',   'Random string',         ['length', 'charset']],
            ['/api/lorem',    'Lorem ipsum',           ['type', 'count']],
            ['/api/ip',       'IP address',            ['count', 'type']],
            ['/api/date',     'Random date',           ['from', 'to', 'format']],
            ['/api/pick',     'Pick from list',        ['items*', 'count', 'unique']],
            ['/api/roll',     'Dice roll',             ['dice', 'count']],
            ['/api/coin',     'Coin flip',             ['count']],
            ['/api/hash',     'Hash a string',         ['value*', 'algo']],
            ['/api/password', 'Secure password',       ['length', 'symbols']],
            ['/api/avatar',   'SVG avatar',            ['seed', 'size', 'style']],
            ['/api/ping',     'Health check',          []],
        ];
        foreach ($endpoints as [$path, $desc, $params]):
        ?>
        <a href="<?= attr($path) ?>" target="_blank" class="endpoint-card" style="text-decoration:none; display:block;">
            <div class="ep-path">GET <?= e($path) ?></div>
            <div class="ep-desc"><?= e($desc) ?></div>
            <?php if ($params): ?>
            <div class="ep-params">
                <?php foreach ($params as $p): ?>
                <span class="ep-param"><?= e($p) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Examples -->
<section id="examples">
    <div class="section-title">// quick examples</div>

    <div class="code-block">
        <div class="code-header">
            <span>cURL — generate 3 UUIDs</span>
            <button class="code-copy" onclick="copyCode(this, 'c1')">copy</button>
        </div>
        <div class="code-content" id="c1">curl <span class="str">"https://rnd.mtex.dev/api/uuid?count=3"</span></div>
    </div>

    <div class="code-block">
        <div class="code-header">
            <span>JavaScript — roll 2d6 five times</span>
            <button class="code-copy" onclick="copyCode(this, 'c2')">copy</button>
        </div>
        <div class="code-content" id="c2"><span class="kw">const</span> res = <span class="kw">await</span> <span class="fn">fetch</span>(<span class="str">'https://rnd.mtex.dev/api/roll?dice=2d6&amp;count=5'</span>);
<span class="kw">const</span> data = <span class="kw">await</span> res.<span class="fn">json</span>();
<span class="cm">// { count: 5, results: [{ result: 9, dice: [4,5], ... }, ...] }</span></div>
    </div>

    <div class="code-block">
        <div class="code-header">
            <span>PHP — random color palette</span>
            <button class="code-copy" onclick="copyCode(this, 'c3')">copy</button>
        </div>
        <div class="code-content" id="c3"><span class="kw">$palette</span> = <span class="fn">json_decode</span>(<span class="fn">file_get_contents</span>(
    <span class="str">'https://rnd.mtex.dev/api/color?count=5&amp;format=hex'</span>
), <span class="kw">true</span>);
<span class="cm">// [['hex'=>'#a3f2c1'], ['hex'=>'#ff6b35'], ...]</span></div>
    </div>

    <div class="code-block">
        <div class="code-header">
            <span>Python — pick from a list</span>
            <button class="code-copy" onclick="copyCode(this, 'c4')">copy</button>
        </div>
        <div class="code-content" id="c4"><span class="kw">import</span> requests
r = requests.<span class="fn">get</span>(<span class="str">'https://rnd.mtex.dev/api/pick'</span>,
    params={<span class="str">'items'</span>: <span class="str">'red,green,blue,yellow'</span>, <span class="str">'count'</span>: <span class="num">2</span>, <span class="str">'unique'</span>: <span class="str">'true'</span>})
<span class="kw">print</span>(r.<span class="fn">json</span>())</div>
    </div>
</section>

<!-- Footer -->
<footer>
    <p>
        <a href="https://mtex.dev">MTEX.dev</a>
        <span class="sep">|</span>
        <a href="https://status.mtex.dev">Status</a>
        <span class="sep">|</span>
        <a href="https://nx.mtex.dev">Nexus API</a>
        <span class="sep">|</span>
        <a href="https://tw.mtex.dev">Tailwind Lib</a>
        <span class="sep">|</span>
        Built with <a href="https://gh.mtex.dev/php-zero">php-zero</a>
    </p>
</footer>

