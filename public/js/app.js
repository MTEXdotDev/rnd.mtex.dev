// ── Live ticker simulation ─────────────────────────────────────────────────────
(function () {
    const ticker = document.getElementById('ticker');
    if (!ticker) return;

    const calls = [
        ['GET', '/api/uuid',                '{ "uuid": "f47ac10b-58cc-4372-a567-0e02b2c3d479" }'],
        ['GET', '/api/name?gender=female',  '{ "first": "Aria", "last": "Walker", "full": "Aria Walker" }'],
        ['GET', '/api/roll?dice=2d20',      '{ "result": 27, "dice": [14,13] }'],
        ['GET', '/api/color?format=hex',    '{ "hex": "#a34fc2" }'],
        ['GET', '/api/number?min=1&max=100','{ "value": 73 }'],
        ['GET', '/api/coin?count=3',        '{ "count": 3, "results": [...] }'],
        ['GET', '/api/password?length=20',  '{ "password": "xK!9mRv#...", "entropy_bits": 131.6 }'],
        ['GET', '/api/lorem?type=sentences','{ "text": "Dolor sit amet consectetur..." }'],
        ['GET', '/api/gradient?type=linear','{ "css": "linear-gradient(217deg, #4f2, #2bf, #f2a)" }'],
        ['GET', '/api/ip?type=v6',          '{ "ipv6": "2a4f:e7b1:c3d9:..." }'],
    ];

    let idx = 0;

    function addLine() {
        const [method, url, resp] = calls[idx % calls.length];
        idx++;

        const line = document.createElement('div');
        line.className = 'ticker-line';
        line.innerHTML =
            `<span class="ticker-method">${method}</span>` +
            `<span class="ticker-url">${url}</span>` +
            `<span class="ticker-resp">\u2192 ${resp.slice(0, 50)}\u2026</span>`;

        ticker.appendChild(line);

        // Keep at most 4 lines visible
        if (ticker.children.length > 4) {
            ticker.removeChild(ticker.firstChild);
        }
    }

    // Seed with 3 lines immediately, then rotate
    addLine(); addLine(); addLine();
    setInterval(addLine, 2200);
}());

// ── MTEX platform status indicator ────────────────────────────────────────────
(function () {
    const dot   = document.getElementById('status-dot');
    const label = document.getElementById('status-label');
    if (!dot || !label) return;

    fetch('https://status.mtex.dev/?type=check', { cache: 'no-store' })
        .then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
        .then(function (data) {
            if (data && data.operational === true) {
                dot.className   = 'status-dot ok';
                label.textContent = 'operational';
            } else {
                dot.className   = 'status-dot degraded';
                label.textContent = 'degraded';
            }
        })
        .catch(function () {
            dot.className   = 'status-dot error';
            label.textContent = 'unknown';
        });
}());

// ── Copy-to-clipboard for code blocks ─────────────────────────────────────────
function copyCode(btn, id) {
    const el = document.getElementById(id);
    if (!el) return;

    navigator.clipboard.writeText(el.innerText).then(function () {
        btn.textContent = 'copied!';
        setTimeout(function () { btn.textContent = 'copy'; }, 1500);
    });
}
