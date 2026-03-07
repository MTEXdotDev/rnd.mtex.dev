<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cache;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Core\Str;
use App\Core\Validator;
use App\Core\View;

/**
 * DemoController — Interactive demonstrations of every PHP-Zero feature.
 *
 * Routes (all registered in routes/web.php under the /demo group):
 *
 *   GET  /demo                          → index()
 *   GET  /demo/routing                  → routing()
 *   GET  /demo/user/{id}                → user(string $id)
 *   GET  /demo/slug/{slug:[a-z0-9-]+}   → slugRoute(string $slug)
 *   GET  /demo/query                    → query()
 *   GET  /demo/forms                    → forms()
 *   POST /demo/forms                    → formsPost()
 *   GET  /demo/database                 → database()
 *   POST /demo/database                 → databasePost()
 *   GET  /demo/cache                    → cacheDemo()
 *   POST /demo/cache                    → cacheDemoPost()
 *   GET  /demo/str                      → str()
 *   GET  /demo/escape                   → escape()
 *   GET  /demo/errors                   → errors()
 *   GET  /demo/trigger/{code:[0-9]{3}}  → triggerError(string $code)
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class DemoController
{
    // ── Shared layout helper ──────────────────────────────────────────────────

    private function render(string $view, array $data = []): string
    {
        // $activeSection drives the sidebar highlight
        return View::render($view, $data, 'demo');
    }

    // ── GET /demo ─────────────────────────────────────────────────────────────

    public function index(): string
    {
        return $this->render('demo/index', [
            'title'         => 'Demo Hub',
            'activeSection' => 'index',
        ]);
    }

    // ── GET /demo/routing ─────────────────────────────────────────────────────

    public function routing(): string
    {
        return $this->render('demo/routing', [
            'title'         => 'Router & Params',
            'breadcrumb'    => 'Router & Params',
            'activeSection' => 'routing',
            'currentUrl'    => Request::uri(),
        ]);
    }

    // ── GET /demo/user/{id} ───────────────────────────────────────────────────

    public function user(string $id): string
    {
        // Demonstrate abort() — non-numeric IDs get a 422
        if (!is_numeric($id)) {
            abort(422, "User ID must be numeric. Got: \"{$id}\"");
        }

        return $this->render('demo/user', [
            'title'         => "User #{$id}",
            'breadcrumb'    => "User #{$id}",
            'activeSection' => 'user',
            'id'            => $id,
            'isNumeric'     => is_numeric($id),
            'method'        => Request::method(),
        ]);
    }

    // ── GET /demo/slug/{slug:[a-z0-9-]+} ─────────────────────────────────────

    public function slugRoute(string $slug): string
    {
        $slugExamples = [
            'Hello, World!'           => Str::slug('Hello, World!'),
            'Héllo Wörld'             => Str::slug('Héllo Wörld'),
            'PHP Zero Framework 2025' => Str::slug('PHP Zero Framework 2025'),
            '__FOO__ BAR__'           => Str::slug('__FOO__ BAR__'),
            'über straße'             => Str::slug('über straße'),
        ];

        $words = array_filter(explode('-', $slug));

        return $this->render('demo/slug', [
            'title'         => "Slug: {$slug}",
            'breadcrumb'    => "Slug Route",
            'activeSection' => 'slug',
            'slug'          => $slug,
            'length'        => Str::length($slug),
            'wordCount'     => count($words),
            'studly'        => Str::studly($slug),
            'titleCase'     => Str::title(str_replace('-', ' ', $slug)),
            'slugExamples'  => $slugExamples,
        ]);
    }

    // ── GET /demo/query ───────────────────────────────────────────────────────

    public function query(): string
    {
        $search   = Request::get('search', '');
        $sort     = Request::get('sort', 'name');
        $page     = (int) Request::get('page', '1');
        $hasSearch = Request::has('search');

        // Collect only known params for the demo table
        $params = array_filter(
            Request::all(),
            static fn (mixed $v) => $v !== null && $v !== ''
        );

        $examples = [
            '/demo/query'                                          => 'no params',
            '/demo/query?search=php-zero'                         => 'search only',
            '/demo/query?search=php-zero&sort=date&page=2'        => 'search + sort + page',
            '/demo/query?search=hello+world&sort=title&page=5'    => 'URL-encoded spaces',
            '/demo/query?filter[]=a&filter[]=b&filter[]=c'        => 'array param',
        ];

        $currentQuery = $_SERVER['QUERY_STRING'] ?? '';

        return $this->render('demo/query', [
            'title'         => 'Query Strings',
            'breadcrumb'    => 'Query Strings',
            'activeSection' => 'query',
            'params'        => $params,
            'search'        => $search,
            'sort'          => $sort,
            'page'          => $page,
            'hasSearch'     => $hasSearch,
            'examples'      => $examples,
            'currentQuery'  => $currentQuery,
        ]);
    }

    // ── GET /demo/forms ───────────────────────────────────────────────────────

    public function forms(): string
    {
        return $this->render('demo/forms', [
            'title'         => 'Form + Validation',
            'breadcrumb'    => 'Form + Validation',
            'activeSection' => 'forms',
        ]);
    }

    // ── POST /demo/forms ──────────────────────────────────────────────────────

    public function formsPost(): string
    {
        $v = Validator::make(Request::all(), [
            'name'     => 'required|min_length:2|max_length:80',
            'username' => 'required|min_length:3|max_length:30|alpha_dash',
            'email'    => 'required|email',
            'age'      => 'nullable|numeric|min:13|max:120',
            'role'     => 'required|in:admin,editor,viewer',
            'bio'      => 'nullable|max_length:300',
        ]);

        if ($v->fails()) {
            // Flash errors and old input for the next GET
            Session::flash('_errors', $v->errors());

            foreach ($v->old() as $key => $value) {
                Session::flash("_old_{$key}", $value);
            }

            redirect('/demo/forms');
        }

        return $this->render('demo/forms', [
            'title'         => 'Form + Validation',
            'breadcrumb'    => 'Form + Validation',
            'activeSection' => 'forms',
            'success'       => true,
            'validated'     => $v->validated(),
        ]);
    }

    // ── GET /demo/database ────────────────────────────────────────────────────

    public function database(): string
    {
        $db = $this->dbSetup();

        $notes = $db->select('SELECT * FROM demo_notes ORDER BY id DESC');
        $count = (int) ($db->scalar('SELECT COUNT(*) FROM demo_notes') ?? 0);

        return $this->render('demo/database', [
            'title'         => 'Database — SQLite',
            'breadcrumb'    => 'Database',
            'activeSection' => 'database',
            'notes'         => $notes,
            'count'         => $count,
            'driver'        => $db->getDriver(),
        ]);
    }

    // ── POST /demo/database ───────────────────────────────────────────────────

    public function databasePost(): string
    {
        $action = Request::post('_action', '');
        $db     = $this->dbSetup();

        match ($action) {
            'add' => (function () use ($db): void {
                $v = Validator::make(Request::all(), [
                    'note' => 'required|min_length:1|max_length:200',
                ]);

                if ($v->fails()) {
                    Session::flash('_errors', $v->errors());
                    redirect('/demo/database');
                }

                $db->insert('demo_notes', [
                    'body'       => Request::post('note', ''),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                Session::flash('db_success', 'Note added.');
            })(),

            'delete' => (function () use ($db): void {
                $id = (int) Request::post('id', '0');

                if ($id > 0) {
                    $db->delete('demo_notes', ['id' => $id]);
                    Session::flash('db_success', "Note #{$id} deleted.");
                }
            })(),

            'flush' => (function () use ($db): void {
                $db->statement('DELETE FROM demo_notes');
                Session::flash('db_success', 'All notes deleted.');
            })(),

            default => null,
        };

        redirect('/demo/database');
    }

    // ── GET /demo/cache ───────────────────────────────────────────────────────

    public function cacheDemo(): string
    {
        $cacheKey = 'demo_counter';

        $isHit = Cache::has($cacheKey);

        // remember() — computed only on a cold miss
        $counter = Cache::remember($cacheKey, 30, static fn () => random_int(1_000, 999_999));

        $ttlRemaining = Cache::ttl($cacheKey);

        // Scan cache directory for all entries
        $cacheDir = defined('BASE_PATH') ? BASE_PATH . '/storage/cache' : '';
        $keys     = [];

        if (is_dir($cacheDir)) {
            foreach (glob($cacheDir . '/*.cache') ?: [] as $file) {
                $raw     = @file_get_contents($file);
                $payload = $raw ? @unserialize($raw) : null;
                $ttl     = 0;

                if (is_array($payload) && isset($payload['expires_at'])) {
                    $ttl = $payload['expires_at'] === 0
                        ? 0
                        : max(-1, $payload['expires_at'] - time());
                }

                $keys[] = [
                    'key'  => basename($file, '.cache'),
                    'ttl'  => $ttl,
                    'size' => Str::formatBytes((int) filesize($file)),
                ];
            }
        }

        return $this->render('demo/cache', [
            'title'         => 'Cache',
            'breadcrumb'    => 'Cache',
            'activeSection' => 'cache',
            'counter'       => $counter,
            'isHit'         => $isHit,
            'ttlRemaining'  => $ttlRemaining,
            'keys'          => $keys,
        ]);
    }

    // ── POST /demo/cache ──────────────────────────────────────────────────────

    public function cacheDemoPost(): string
    {
        $action = Request::post('_action', '');

        match ($action) {
            'bust'  => Cache::forget('demo_counter'),
            'flush' => Cache::flush(),
            default => null,
        };

        redirect('/demo/cache');
    }

    // ── GET /demo/str ─────────────────────────────────────────────────────────

    public function str(): string
    {
        $input = 'user_profile_page';
        $long  = 'The quick brown fox jumps over the lazy dog near the riverbank';
        $email = 'alice.smith@example.com';
        $card  = '4111111111111111';

        $caseExamples = [
            "Str::studly('{$input}')"   => Str::studly($input),
            "Str::camel('{$input}')"    => Str::camel($input),
            "Str::snake('UserProfile')" => Str::snake('UserProfile'),
            "Str::kebab('UserProfile')" => Str::kebab('UserProfile'),
            "Str::title('hello world')" => Str::title('hello world'),
        ];

        $slugExamples = [
            'Hello, World!'           => Str::slug('Hello, World!'),
            'Héllo Wörld'             => Str::slug('Héllo Wörld'),
            'PHP Zero Framework 2025' => Str::slug('PHP Zero Framework 2025'),
            'über straße'             => Str::slug('über straße'),
            '__FOO__  BAR__'          => Str::slug('__FOO__  BAR__'),
        ];

        $truncateExamples = [
            "Str::truncate(\$long, 30)"         => Str::truncate($long, 30),
            "Str::truncate(\$long, 30, '...')"  => Str::truncate($long, 30, '...'),
            "Str::words(\$long, 5)"             => Str::words($long, 5),
            "Str::words(\$long, 3, ' [more]')"  => Str::words($long, 3, ' [more]'),
        ];

        $maskExamples = [
            "Str::mask(\$email, '*', 4)"         => Str::mask($email, '*', 4),
            "Str::mask(\$email, '*', 2, 4)"      => Str::mask($email, '*', 2, 4),
            "Str::mask(\$card, 'x', 0, 4)"       => Str::mask($card, 'x', 0, 4),
            "Str::mask('secret', '#', 2)"        => Str::mask('secret', '#', 2),
        ];

        $utilExamples = [
            "Str::contains('foobar', 'oba')"        => Str::contains('foobar', 'oba'),
            "Str::startsWith('foobar', 'foo')"      => Str::startsWith('foobar', 'foo'),
            "Str::endsWith('foobar', 'bar')"        => Str::endsWith('foobar', 'bar'),
            "Str::between('<b>hi</b>','<b>','</b>')"=> Str::between('<b>hi</b>', '<b>', '</b>'),
            "Str::formatBytes(1_048_576)"           => Str::formatBytes(1_048_576),
            "Str::formatBytes(1536)"                => Str::formatBytes(1536),
            "Str::length('héllo')"                  => (string) Str::length('héllo'),
            "Str::isJson('{\"ok\":true}')"           => Str::isJson('{"ok":true}'),
        ];

        return $this->render('demo/str', [
            'title'            => 'Str Utilities',
            'breadcrumb'       => 'Str',
            'activeSection'    => 'str',
            'input'            => $input,
            'caseExamples'     => $caseExamples,
            'slugExamples'     => $slugExamples,
            'truncateExamples' => $truncateExamples,
            'maskExamples'     => $maskExamples,
            'utilExamples'     => $utilExamples,
            'random32'         => Str::random(32),
            'random16'         => Str::random(16),
            'uuid1'            => Str::uuid(),
            'uuid2'            => Str::uuid(),
        ]);
    }

    // ── GET /demo/escape ──────────────────────────────────────────────────────

    public function escape(): string
    {
        return $this->render('demo/escape', [
            'title'         => 'Output Escaping',
            'breadcrumb'    => 'Output Escaping',
            'activeSection' => 'escape',
        ]);
    }

    // ── GET /demo/errors ──────────────────────────────────────────────────────

    public function errors(): string
    {
        $errorCodes = [
            400 => ['label' => 'Bad Request',    'desc' => 'Malformed request',   'tag' => 'tag-amber'],
            401 => ['label' => 'Unauthorized',   'desc' => 'Auth required',       'tag' => 'tag-amber'],
            403 => ['label' => 'Forbidden',      'desc' => 'Access denied',       'tag' => 'tag-rose'],
            404 => ['label' => 'Not Found',      'desc' => 'Resource missing',    'tag' => 'tag-muted'],
            405 => ['label' => 'Method Not Allowed', 'desc' => 'Wrong verb',      'tag' => 'tag-amber'],
            409 => ['label' => 'Conflict',       'desc' => 'State conflict',      'tag' => 'tag-amber'],
            410 => ['label' => 'Gone',           'desc' => 'Permanently removed', 'tag' => 'tag-muted'],
            422 => ['label' => 'Unprocessable',  'desc' => 'Validation failed',   'tag' => 'tag-violet'],
            429 => ['label' => 'Too Many Requests', 'desc' => 'Rate limited',     'tag' => 'tag-amber'],
            500 => ['label' => 'Server Error',   'desc' => 'Internal failure',    'tag' => 'tag-rose'],
            503 => ['label' => 'Unavailable',    'desc' => 'Service down',        'tag' => 'tag-rose'],
        ];

        return $this->render('demo/errors', [
            'title'         => 'Error Pages',
            'breadcrumb'    => 'Error Pages',
            'activeSection' => 'errors',
            'errorCodes'    => $errorCodes,
        ]);
    }

    // ── GET /demo/trigger/{code} ──────────────────────────────────────────────

    public function triggerError(string $code): never
    {
        $messages = [
            '400' => 'The request was malformed or contained invalid parameters.',
            '401' => 'Authentication is required to access this resource.',
            '403' => 'You do not have permission to access this resource.',
            '404' => 'The page or resource you requested could not be found.',
            '405' => 'The HTTP method used is not allowed for this endpoint.',
            '409' => 'The request conflicts with the current state of the resource.',
            '410' => 'This resource has been permanently removed.',
            '422' => 'The submitted data failed validation.',
            '429' => 'You have sent too many requests. Please slow down.',
            '500' => 'An unexpected error occurred on the server.',
            '503' => 'The service is temporarily unavailable. Try again shortly.',
        ];

        $message = $messages[$code] ?? "HTTP Error {$code}";

        abort((int) $code, $message);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Return a Database instance with the demo_notes table guaranteed to exist.
     */
    private function dbSetup(): Database
    {
        $db = Database::getInstance();

        if (!$db->tableExists('demo_notes')) {
            $db->statement('
                CREATE TABLE demo_notes (
                    id         INTEGER PRIMARY KEY AUTOINCREMENT,
                    body       TEXT    NOT NULL,
                    created_at TEXT    NOT NULL
                )
            ');
        }

        return $db;
    }
}
