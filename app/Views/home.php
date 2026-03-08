<?php
// Define styles to be injected into the layout <style> block
$content_styles = '
    /* ── Nav ── */
    nav {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1.25rem 2rem;
        border-bottom: 1px solid var(--border);
        position: sticky; top: 0;
        background: rgba(10,10,15,.85);
        backdrop-filter: blur(12px);
        z-index: 100;
    }
    .nav-logo {
        font-family: var(--mono);
        font-size: .95rem;
        color: var(--text);
        text-decoration: none;
        display: flex; align-items: center; gap: .6rem;
    }
    .nav-logo .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); display: inline-block; animation: pulse 2s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }
    .nav-links { display: flex; gap: 1.5rem; }
    .nav-links a { font-size: .85rem; color: var(--muted); text-decoration: none; transition: color .2s; font-family: var(--mono); }
    .nav-links a:hover { color: var(--text); }

    /* ── Hero ── */
    .hero {
        max-width: 900px; margin: 0 auto;
        padding: 5rem 2rem 3rem;
        text-align: center;
    }
    .hero-badge {
        display: inline-flex; align-items: center; gap: .5rem;
        background: rgba(124,58,237,.12);
        border: 1px solid rgba(124,58,237,.3);
        border-radius: 999px;
        padding: .35rem .9rem;
        font-family: var(--mono);
        font-size: .75rem;
        color: #a78bfa;
        margin-bottom: 2rem;
    }
    .hero h1 {
        font-family: var(--mono);
        font-size: clamp(2.2rem, 6vw, 4.5rem);
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 1.5rem;
        letter-spacing: -.03em;
    }
    .hero h1 .grad {
        background: linear-gradient(135deg, #a78bfa 0%, #06b6d4 60%, #f59e0b 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .hero p {
        font-size: 1.1rem;
        color: var(--muted);
        max-width: 540px;
        margin: 0 auto 2.5rem;
        line-height: 1.7;
        font-weight: 300;
    }
    .hero-actions { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
    .btn {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .65rem 1.4rem;
        border-radius: 6px;
        font-family: var(--mono);
        font-size: .85rem;
        text-decoration: none;
        transition: all .2s;
        cursor: pointer; border: none;
    }
    .btn-primary {
        background: var(--accent);
        color: #fff;
    }
    .btn-primary:hover { background: #6d28d9; transform: translateY(-1px); }
    .btn-ghost {
        background: transparent;
        color: var(--muted);
        border: 1px solid var(--border);
    }
    .btn-ghost:hover { border-color: var(--muted); color: var(--text); }

    /* ── Live demo ticker ── */
    .ticker {
        margin: 3rem auto 0;
        max-width: 680px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
    }
    .ticker-bar {
        background: var(--border);
        padding: .5rem 1rem;
        display: flex; align-items: center; gap: .5rem;
        font-family: var(--mono); font-size: .72rem; color: var(--muted);
    }
    .ticker-dot { width: 8px; height: 8px; border-radius: 50%; background: #10b981; }
    .ticker-body {
        padding: 1.2rem 1.4rem;
        font-family: var(--mono);
        font-size: .8rem;
        line-height: 2;
        overflow: hidden;
        min-height: 120px;
        position: relative;
    }
    .ticker-line {
        display: flex; gap: 1rem;
        opacity: 0;
        animation: fadeIn .5s ease forwards;
    }
    @keyframes fadeIn { to { opacity: 1; } }
    .ticker-method { color: #86efac; }
    .ticker-url    { color: #93c5fd; }
    .ticker-resp   { color: #fde68a; }

    /* ── Stats strip ── */
    .stats {
        display: flex; justify-content: center; gap: 0;
        max-width: 680px; margin: 2rem auto;
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
    }
    .stat {
        flex: 1; padding: 1.2rem;
        text-align: center;
        border-right: 1px solid var(--border);
    }
    .stat:last-child { border-right: none; }
    .stat-num { font-family: var(--mono); font-size: 1.6rem; font-weight: 700; color: var(--text); }
    .stat-lbl { font-size: .75rem; color: var(--muted); margin-top: .2rem; }

    /* ── Endpoints grid ── */
    section { max-width: 900px; margin: 0 auto; padding: 3rem 2rem; }
    .section-title {
        font-family: var(--mono);
        font-size: .75rem;
        color: var(--accent2);
        letter-spacing: .12em;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
    }
    .endpoints-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 1px;
        background: var(--border);
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
    }
    .endpoint-card {
        background: var(--surface);
        padding: 1.3rem 1.4rem;
        transition: background .2s;
        cursor: default;
    }
    .endpoint-card:hover { background: #16161f; }
    .ep-path {
        font-family: var(--mono); font-size: .88rem;
        color: var(--accent2);
        margin-bottom: .4rem;
    }
    .ep-desc { font-size: .82rem; color: var(--muted); line-height: 1.5; }
    .ep-params {
        margin-top: .6rem;
        display: flex; flex-wrap: wrap; gap: .3rem;
    }
    .ep-param {
        font-family: var(--mono); font-size: .68rem;
        background: rgba(6,182,212,.08);
        border: 1px solid rgba(6,182,212,.2);
        color: #67e8f9;
        padding: .15rem .45rem;
        border-radius: 4px;
    }

    /* ── Code example ── */
    .code-block {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .code-header {
        background: var(--border);
        padding: .6rem 1rem;
        display: flex; align-items: center; justify-content: space-between;
        font-family: var(--mono); font-size: .72rem; color: var(--muted);
    }
    .code-copy {
        cursor: pointer; background: none; border: 1px solid var(--muted);
        color: var(--muted); border-radius: 4px; padding: .2rem .5rem;
        font-family: var(--mono); font-size: .68rem; transition: all .2s;
    }
    .code-copy:hover { border-color: var(--text); color: var(--text); }
    .code-content {
        padding: 1.2rem 1.4rem;
        font-family: var(--mono); font-size: .8rem; line-height: 1.8;
        overflow-x: auto;
        color: var(--text);
    }
    .kw  { color: #f472b6; }
    .str { color: #86efac; }
    .fn  { color: #93c5fd; }
    .cm  { color: #475569; }
    .num { color: #fde68a; }

    /* ── Footer ── */
    footer {
        border-top: 1px solid var(--border);
        padding: 2rem;
        text-align: center;
        font-family: var(--mono);
        font-size: .75rem;
        color: var(--muted);
    }
    footer a { color: var(--muted); text-decoration: none; }
    footer a:hover { color: var(--text); }
    footer .sep { margin: 0 .75rem; opacity: .3; }
';
?>

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
<div class="stats" style="margin: 0 auto 3rem; max-width: 680px; padding: 0 2rem;">
    <div style="max-width: 680px; width: 100%; margin: 0 auto; display: flex;">
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

<script>
// ── Live ticker simulation ────────────────────────────────────────────────────
const calls = [
    ['GET', '/api/uuid', '{ "uuid": "f47ac10b-58cc-4372-a567-0e02b2c3d479" }'],
    ['GET', '/api/name?gender=female', '{ "first": "Aria", "last": "Walker", "full": "Aria Walker" }'],
    ['GET', '/api/roll?dice=2d20', '{ "result": 27, "dice": [14,13] }'],
    ['GET', '/api/color?format=hex', '{ "hex": "#a34fc2" }'],
    ['GET', '/api/number?min=1&max=100', '{ "value": 73 }'],
    ['GET', '/api/coin?count=3', '{ "count": 3, "results": [...] }'],
    ['GET', '/api/password?length=20', '{ "password": "xK!9mRv#...", "entropy_bits": 131.6 }'],
    ['GET', '/api/lorem?type=sentences', '{ "text": "Dolor sit amet consectetur..." }'],
    ['GET', '/api/gradient?type=linear', '{ "css": "linear-gradient(217deg, #4f2, #2bf, #f2a)" }'],
    ['GET', '/api/ip?type=v6', '{ "ipv6": "2a4f:e7b1:c3d9:..." }'],
];

let idx = 0;
const ticker = document.getElementById('ticker');

function addLine() {
    const [method, url, resp] = calls[idx % calls.length];
    idx++;
    const line = document.createElement('div');
    line.className = 'ticker-line';
    line.style.animationDelay = '0s';
    line.innerHTML = `<span class="ticker-method">${method}</span><span class="ticker-url">${url}</span><span class="ticker-resp">→ ${resp.slice(0,50)}…</span>`;
    ticker.appendChild(line);
    if (ticker.children.length > 4) ticker.removeChild(ticker.firstChild);
}

addLine(); addLine(); addLine();
setInterval(addLine, 2200);

// ── Copy code ─────────────────────────────────────────────────────────────────
function copyCode(btn, id) {
    const el = document.getElementById(id);
    const text = el.innerText;
    navigator.clipboard.writeText(text).then(() => {
        btn.textContent = 'copied!';
        setTimeout(() => btn.textContent = 'copy', 1500);
    });
}
</script>
