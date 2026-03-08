<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class RndController
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Clamp a count param between 1 and $max. */
    private function count(int $max = 100): int
    {
        return max(1, min($max, (int) Request::get('count', '1')));
    }

    /** Wrap single-result or multi-result under a consistent envelope. */
    private function respond(mixed $single, mixed $multi, int $count): never
    {
        if ($count === 1) {
            Response::make()
                ->noCache()
                ->header('X-RND-Count', '1')
                ->json($single);
        }
        Response::make()
            ->noCache()
            ->header('X-RND-Count', (string) $count)
            ->json(['count' => $count, 'results' => $multi]);
    }

    /** Generate a truly random UUID v4. */
    private function generateUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    // ── Meta endpoints ────────────────────────────────────────────────────────

    public function ping(): never
    {
        Response::toJson([
            'pong'    => true,
            'service' => 'rnd.mtex.dev',
            'version' => '1.0.0',
            'time'    => date('c'),
        ]);
    }

    public function endpoints(): never
    {
        Response::toJson([
            'service'   => 'rnd.mtex.dev',
            'base_url'  => 'https://rnd.mtex.dev/api',
            'endpoints' => [
                ['method' => 'GET', 'path' => '/ping',     'description' => 'Health check'],
                ['method' => 'GET', 'path' => '/endpoints','description' => 'This list'],
                ['method' => 'GET', 'path' => '/uuid',     'description' => 'UUID v4',               'params' => ['count']],
                ['method' => 'GET', 'path' => '/name',     'description' => 'Random person name',     'params' => ['count', 'gender:male|female|any']],
                ['method' => 'GET', 'path' => '/email',    'description' => 'Random email address',   'params' => ['count', 'domain']],
                ['method' => 'GET', 'path' => '/color',    'description' => 'Random color',           'params' => ['count', 'format:hex|rgb|hsl|all']],
                ['method' => 'GET', 'path' => '/gradient', 'description' => 'Random CSS gradient',    'params' => ['count', 'type:linear|radial|conic']],
                ['method' => 'GET', 'path' => '/number',   'description' => 'Random number',          'params' => ['count', 'min', 'max', 'float:true|false']],
                ['method' => 'GET', 'path' => '/string',   'description' => 'Random string',          'params' => ['count', 'length', 'charset:alpha|alphanum|hex|numeric|symbols|all']],
                ['method' => 'GET', 'path' => '/lorem',    'description' => 'Lorem ipsum text',       'params' => ['count', 'type:words|sentences|paragraphs']],
                ['method' => 'GET', 'path' => '/ip',       'description' => 'Random IP address',      'params' => ['count', 'type:v4|v6|both']],
                ['method' => 'GET', 'path' => '/date',     'description' => 'Random date',            'params' => ['count', 'from', 'to', 'format']],
                ['method' => 'GET', 'path' => '/pick',     'description' => 'Pick from a list',       'params' => ['items (required, comma-separated)', 'count', 'unique:true|false']],
                ['method' => 'GET', 'path' => '/roll',     'description' => 'Dice roll',              'params' => ['dice (notation e.g. 2d6)', 'count']],
                ['method' => 'GET', 'path' => '/coin',     'description' => 'Coin flip',              'params' => ['count']],
                ['method' => 'GET', 'path' => '/hash',     'description' => 'Hash a string',          'params' => ['value (required)', 'algo:md5|sha1|sha256|sha512']],
                ['method' => 'GET', 'path' => '/password', 'description' => 'Secure random password', 'params' => ['count', 'length', 'symbols:true|false']],
                ['method' => 'GET', 'path' => '/avatar',   'description' => 'SVG avatar data URI',    'params' => ['seed', 'size', 'style:geometric|initials|pixel']],
            ],
        ]);
    }

    // ── /api/uuid ─────────────────────────────────────────────────────────────

    public function uuid(): never
    {
        $count = $this->count(100);
        $uuids = array_map(fn() => $this->generateUuid(), range(1, $count));

        $this->respond(
            ['uuid' => $uuids[0]],
            array_map(fn($u) => ['uuid' => $u], $uuids),
            $count
        );
    }

    // ── /api/name ─────────────────────────────────────────────────────────────

    public function name(): never
    {
        $count  = $this->count(50);
        $gender = Request::get('gender', 'any');

        $firstMale   = ['James','Liam','Noah','Oliver','Elijah','Lucas','Mason','Logan','Ethan','Aiden','Henry','Jackson','Sebastian','Carter','Owen','Wyatt','Caleb','Ryan','Nathan','Isaac','Leo','Mateo','Julian','Ezra','Adrian','Finn','Jasper','Theo','Oscar','Felix'];
        $firstFemale = ['Olivia','Emma','Ava','Charlotte','Sophia','Amelia','Isabella','Mia','Evelyn','Harper','Luna','Camila','Gianna','Aria','Scarlett','Riley','Nora','Lily','Zoe','Stella','Hazel','Aurora','Violet','Savannah','Clara','Ellie','Layla','Chloe','Penelope','Grace'];
        $lastNames   = ['Smith','Johnson','Williams','Brown','Jones','Garcia','Miller','Davis','Rodriguez','Martinez','Hernandez','Lopez','Gonzalez','Wilson','Anderson','Thomas','Taylor','Moore','Jackson','Martin','Lee','Perez','Thompson','White','Harris','Sanchez','Clark','Lewis','Robinson','Walker'];

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $g = $gender === 'any' ? (random_int(0, 1) === 0 ? 'male' : 'female') : $gender;
            $pool = $g === 'male' ? $firstMale : $firstFemale;
            $first = $pool[array_rand($pool)];
            $last  = $lastNames[array_rand($lastNames)];
            $results[] = ['first' => $first, 'last' => $last, 'full' => "$first $last", 'gender' => $g];
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/email ────────────────────────────────────────────────────────────

    public function email(): never
    {
        $count  = $this->count(50);
        $domain = Request::get('domain', '');
        $domains = ['gmail.com','yahoo.com','outlook.com','protonmail.com','icloud.com','fastmail.com','hey.com','tutanota.com','zoho.com','aol.com'];
        $adjectives = ['cool','fast','happy','bright','clever','swift','bold','neat','sharp','slick'];
        $nouns      = ['fox','wolf','bear','hawk','owl','lion','tiger','eagle','shark','panda'];

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $adj  = $adjectives[array_rand($adjectives)];
            $noun = $nouns[array_rand($nouns)];
            $num  = random_int(10, 9999);
            $user = "{$adj}{$noun}{$num}";
            $d    = $domain ?: $domains[array_rand($domains)];
            $results[] = ['email' => "{$user}@{$d}", 'username' => $user, 'domain' => $d];
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/color ────────────────────────────────────────────────────────────

    public function color(): never
    {
        $count  = $this->count(50);
        $format = Request::get('format', 'all');

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $r = random_int(0, 255);
            $g = random_int(0, 255);
            $b = random_int(0, 255);
            $hex = sprintf('#%02x%02x%02x', $r, $g, $b);

            // RGB → HSL
            $rn = $r / 255; $gn = $g / 255; $bn = $b / 255;
            $max = max($rn, $gn, $bn); $min = min($rn, $gn, $bn);
            $l = ($max + $min) / 2;
            $d = $max - $min;
            $s = $d < 0.00001 ? 0 : $d / (1 - abs(2 * $l - 1));
            if ($d < 0.00001) {
                $h = 0;
            } elseif ($max == $rn) {
                $h = 60 * fmod(($gn - $bn) / $d, 6);
            } elseif ($max == $gn) {
                $h = 60 * (($bn - $rn) / $d + 2);
            } else {
                $h = 60 * (($rn - $gn) / $d + 4);
            }
            if ($h < 0) $h += 360;

            $color = match($format) {
                'hex' => ['hex' => $hex],
                'rgb' => ['rgb' => ['r' => $r, 'g' => $g, 'b' => $b], 'rgb_string' => "rgb($r,$g,$b)"],
                'hsl' => ['hsl' => ['h' => round($h), 's' => round($s * 100), 'l' => round($l * 100)], 'hsl_string' => sprintf('hsl(%d,%d%%,%d%%)', round($h), round($s*100), round($l*100))],
                default => [
                    'hex'        => $hex,
                    'rgb'        => ['r' => $r, 'g' => $g, 'b' => $b],
                    'rgb_string' => "rgb($r,$g,$b)",
                    'hsl'        => ['h' => round($h), 's' => round($s * 100), 'l' => round($l * 100)],
                    'hsl_string' => sprintf('hsl(%d,%d%%,%d%%)', round($h), round($s*100), round($l*100)),
                ],
            };
            $results[] = $color;
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/gradient ─────────────────────────────────────────────────────────

    public function gradient(): never
    {
        $count = $this->count(20);
        $type  = Request::get('type', 'linear');

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $numColors = random_int(2, 4);
            $colors    = [];
            for ($c = 0; $c < $numColors; $c++) {
                $colors[] = sprintf('#%02x%02x%02x', random_int(0,255), random_int(0,255), random_int(0,255));
            }
            $angle   = random_int(0, 359);
            $joined  = implode(', ', $colors);
            $css     = match ($type) {
                'radial' => "radial-gradient(circle, $joined)",
                'conic'  => "conic-gradient(from {$angle}deg, $joined)",
                default  => "linear-gradient({$angle}deg, $joined)",
            };
            $results[] = ['css' => $css, 'type' => $type === 'linear' ? 'linear' : $type, 'angle' => $angle, 'colors' => $colors];
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/number ───────────────────────────────────────────────────────────

    public function number(): never
    {
        $count = $this->count(100);
        $min   = (float) Request::get('min', '0');
        $max   = (float) Request::get('max', '100');
        $float = filter_var(Request::get('float', 'false'), FILTER_VALIDATE_BOOLEAN);

        if ($min > $max) [$min, $max] = [$max, $min];

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $val     = $float
                ? round($min + lcg_value() * ($max - $min), 4)
                : random_int((int) $min, (int) $max);
            $results[] = ['value' => $val, 'min' => $min, 'max' => $max];
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/string ───────────────────────────────────────────────────────────

    public function randomString(): never
    {
        $count   = $this->count(50);
        $length  = max(1, min(512, (int) Request::get('length', '16')));
        $charset = Request::get('charset', 'alphanum');

        $charsets = [
            'alpha'    => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'alphanum' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            'hex'      => '0123456789abcdef',
            'numeric'  => '0123456789',
            'symbols'  => '!@#$%^&*()-_=+[]{}|;:,.<>?',
            'all'      => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{}|;:,.<>?',
        ];
        $pool = $charsets[$charset] ?? $charsets['alphanum'];
        $poolLen = strlen($pool);

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $str = '';
            for ($j = 0; $j < $length; $j++) {
                $str .= $pool[random_int(0, $poolLen - 1)];
            }
            $results[] = ['value' => $str, 'length' => $length, 'charset' => $charset];
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/lorem ────────────────────────────────────────────────────────────

    public function lorem(): never
    {
        $count = $this->count(20);
        $type  = Request::get('type', 'sentences');
        $n     = max(1, min(50, (int) Request::get('count', '3')));

        $words = explode(' ', 'lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ut enim ad minim veniam quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur excepteur sint occaecat cupidatat non proident sunt in culpa qui officia deserunt mollit anim id est laborum pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas vestibulum tortor quam feugiat vitae ultricies eget tempor sit amet ante ipsum primis in faucibus');

        $makeSentence = function () use ($words): string {
            $len = random_int(6, 14);
            $picked = [];
            for ($i = 0; $i < $len; $i++) {
                $picked[] = $words[array_rand($words)];
            }
            return ucfirst(implode(' ', $picked)) . '.';
        };

        $makeParagraph = function () use ($makeSentence): string {
            $sentences = random_int(3, 6);
            $parts = [];
            for ($i = 0; $i < $sentences; $i++) {
                $parts[] = $makeSentence();
            }
            return implode(' ', $parts);
        };

        $makeWords = function (int $n) use ($words): string {
            $picked = [];
            for ($i = 0; $i < $n; $i++) {
                $picked[] = $words[array_rand($words)];
            }
            return implode(' ', $picked);
        };

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $text = match ($type) {
                'words'      => $makeWords($n),
                'paragraphs' => implode("\n\n", array_map(fn() => $makeParagraph(), range(1, $n))),
                default      => implode(' ', array_map(fn() => $makeSentence(), range(1, $n))),
            };
            $results[] = ['text' => $text, 'type' => $type, 'count' => $n];
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/ip ───────────────────────────────────────────────────────────────

    public function ip(): never
    {
        $count = $this->count(50);
        $type  = Request::get('type', 'v4');

        $makeV4 = fn() => implode('.', [random_int(1,254), random_int(0,255), random_int(0,255), random_int(1,254)]);
        $makeV6 = function (): string {
            $groups = [];
            for ($i = 0; $i < 8; $i++) {
                $groups[] = sprintf('%04x', random_int(0, 0xffff));
            }
            return implode(':', $groups);
        };

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $results[] = match ($type) {
                'v6'   => ['ipv6' => $makeV6(), 'version' => 6],
                'both' => ['ipv4' => $makeV4(), 'ipv6' => $makeV6()],
                default => ['ipv4' => $makeV4(), 'version' => 4],
            };
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/date ─────────────────────────────────────────────────────────────

    public function date(): never
    {
        $count  = $this->count(50);
        $format = Request::get('format', 'Y-m-d');
        $from   = Request::get('from', '1970-01-01');
        $to     = Request::get('to',   date('Y-m-d'));

        $fromTs = strtotime($from) ?: mktime(0,0,0,1,1,1970);
        $toTs   = strtotime($to)   ?: time();
        if ($fromTs > $toTs) [$fromTs, $toTs] = [$toTs, $fromTs];

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $ts = random_int($fromTs, $toTs);
            $results[] = [
                'date'      => date($format, $ts),
                'timestamp' => $ts,
                'iso8601'   => date('c', $ts),
                'format'    => $format,
            ];
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/pick ─────────────────────────────────────────────────────────────

    public function pick(): never
    {
        $itemsRaw = Request::get('items', '');
        if (empty($itemsRaw)) {
            Response::make()->status(422)->json(['error' => 'Missing required param: items (comma-separated list)']);
        }

        $items  = array_values(array_filter(array_map('trim', explode(',', $itemsRaw))));
        $count  = $this->count(count($items));
        $unique = filter_var(Request::get('unique', 'false'), FILTER_VALIDATE_BOOLEAN);

        if ($unique && $count > count($items)) {
            Response::make()->status(422)->json(['error' => "Cannot pick $count unique items from a list of " . count($items)]);
        }

        if ($unique) {
            $shuffled = $items;
            shuffle($shuffled);
            $picked = array_slice($shuffled, 0, $count);
            $results = array_map(fn($v) => ['value' => $v, 'index' => array_search($v, $items)], $picked);
        } else {
            $results = [];
            for ($i = 0; $i < $count; $i++) {
                $idx = array_rand($items);
                $results[] = ['value' => $items[$idx], 'index' => $idx];
            }
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/roll ─────────────────────────────────────────────────────────────

    public function roll(): never
    {
        $notation = Request::get('dice', '1d6');
        $count    = $this->count(20);

        if (!preg_match('/^(\d+)d(\d+)([+-]\d+)?$/i', trim($notation), $m)) {
            Response::make()->status(422)->json([
                'error'   => 'Invalid dice notation. Use format: NdS or NdS+M (e.g. 2d6, 4d8+2)',
                'example' => ['1d6', '2d20', '3d8+5', '1d100'],
            ]);
        }

        $numDice   = min((int)$m[1], 100);
        $sides     = min((int)$m[2], 1000);
        $modifier  = isset($m[3]) ? (int)$m[3] : 0;
        $notation  = "{$numDice}d{$sides}" . ($modifier !== 0 ? sprintf('%+d', $modifier) : '');

        $results = [];
        for ($r = 0; $r < $count; $r++) {
            $dice = [];
            for ($d = 0; $d < $numDice; $d++) {
                $dice[] = random_int(1, $sides);
            }
            $sum = array_sum($dice) + $modifier;
            $results[] = [
                'result'   => $sum,
                'dice'     => $dice,
                'modifier' => $modifier,
                'notation' => $notation,
                'min'      => $numDice + $modifier,
                'max'      => ($numDice * $sides) + $modifier,
            ];
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/coin ─────────────────────────────────────────────────────────────

    public function coin(): never
    {
        $count = $this->count(100);
        $faces = ['heads', 'tails'];
        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $face = $faces[random_int(0, 1)];
            $results[] = ['result' => $face, 'heads' => $face === 'heads'];
        }
        $this->respond($results[0], $results, $count);
    }

    // ── /api/hash ─────────────────────────────────────────────────────────────

    public function hash(): never
    {
        $value = Request::get('value', '');
        if ($value === '') {
            Response::make()->status(422)->json(['error' => 'Missing required param: value']);
        }

        $algo    = Request::get('algo', 'sha256');
        $allowed = ['md5', 'sha1', 'sha256', 'sha512'];
        if (!in_array($algo, $allowed, true)) {
            Response::make()->status(422)->json(['error' => "Invalid algo. Choose from: " . implode(', ', $allowed)]);
        }

        $hash = $algo === 'md5' ? md5($value) : hash($algo, $value);

        Response::make()->noCache()->json([
            'input'     => $value,
            'algorithm' => $algo,
            'hash'      => $hash,
            'length'    => strlen($hash),
        ]);
    }

    // ── /api/password ─────────────────────────────────────────────────────────

    public function password(): never
    {
        $count   = $this->count(20);
        $length  = max(8, min(128, (int) Request::get('length', '16')));
        $symbols = filter_var(Request::get('symbols', 'true'), FILTER_VALIDATE_BOOLEAN);

        $upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower   = 'abcdefghijklmnopqrstuvwxyz';
        $digits  = '0123456789';
        $special = '!@#$%^&*-_=+?';
        $pool    = $upper . $lower . $digits . ($symbols ? $special : '');
        $poolLen = strlen($pool);

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            // Guarantee at least one of each required character class
            $pwd  = '';
            $pwd .= $upper[random_int(0, 25)];
            $pwd .= $lower[random_int(0, 25)];
            $pwd .= $digits[random_int(0, 9)];
            if ($symbols) $pwd .= $special[random_int(0, strlen($special) - 1)];
            while (strlen($pwd) < $length) {
                $pwd .= $pool[random_int(0, $poolLen - 1)];
            }
            // Shuffle so required chars aren't always at start
            $shuffled = str_split($pwd);
            shuffle($shuffled);
            $final = implode('', $shuffled);

            // Entropy estimate (bits)
            $entropy = round($length * log($poolLen, 2), 1);

            $results[] = ['password' => $final, 'length' => $length, 'entropy_bits' => $entropy, 'symbols' => $symbols];
        }

        $this->respond($results[0], $results, $count);
    }

    // ── /api/avatar ───────────────────────────────────────────────────────────

    public function avatar(): never
    {
        $seed  = Request::get('seed', (string) random_int(0, PHP_INT_MAX));
        $size  = max(32, min(512, (int) Request::get('size', '80')));
        $style = Request::get('style', 'geometric');

        // Deterministic random from seed
        srand((int) abs(crc32($seed)));

        $hue1 = rand(0, 359);
        $hue2 = ($hue1 + 120 + rand(-30, 30)) % 360;
        $bg   = "hsl($hue1, 65%, 55%)";
        $fg   = "hsl($hue2, 75%, 35%)";

        $shapes = '';
        if ($style === 'geometric') {
            for ($i = 0; $i < 6; $i++) {
                $x  = rand(0, $size); $y = rand(0, $size);
                $r  = rand(8, (int)($size * 0.4));
                $op = round(rand(40, 85) / 100, 2);
                $shapes .= "<circle cx='$x' cy='$y' r='$r' fill='$fg' opacity='$op'/>";
            }
            for ($i = 0; $i < 4; $i++) {
                $x = rand(0, $size); $y = rand(0, $size);
                $w = rand(10, (int)($size * 0.5)); $h = rand(10, (int)($size * 0.5));
                $rot = rand(0, 90);
                $op  = round(rand(30, 70) / 100, 2);
                $shapes .= "<rect x='$x' y='$y' width='$w' height='$h' rx='3' fill='$bg' opacity='$op' transform='rotate($rot,$x,$y)'/>";
            }
        } elseif ($style === 'pixel') {
            $grid = 8;
            $cell = (int)($size / $grid);
            for ($row = 0; $row < $grid; $row++) {
                for ($col = 0; $col < $grid; $col++) {
                    if (rand(0,1)) {
                        $px = $col * $cell; $py = $row * $cell;
                        $shapes .= "<rect x='$px' y='$py' width='$cell' height='$cell' fill='$fg'/>";
                    }
                }
            }
        } else {
            // initials
            $initials = strtoupper(substr($seed, 0, min(2, strlen($seed))));
            $shapes   = "<text x='" . ($size/2) . "' y='" . ($size*0.65) . "' font-family='monospace' font-size='" . ($size*0.4) . "' fill='$fg' text-anchor='middle'>$initials</text>";
        }

        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='$size' height='$size' viewBox='0 0 $size $size'>"
             . "<rect width='$size' height='$size' fill='$bg'/>"
             . $shapes
             . "</svg>";

        $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);

        Response::make()->noCache()->json([
            'seed'     => $seed,
            'size'     => $size,
            'style'    => $style,
            'data_uri' => $dataUri,
            'svg'      => $svg,
        ]);
    }
}
