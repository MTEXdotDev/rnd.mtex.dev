<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Str — Immutable string utility class.
 *
 * All methods are pure static and work on PHP strings directly.
 *
 * Usage:
 *   Str::slug('Hello World!');              // 'hello-world'
 *   Str::truncate($bio, 160);               // 'Lorem ipsum…'
 *   Str::studly('user_profile_page');       // 'UserProfilePage'
 *   Str::camel('user_profile_page');        // 'userProfilePage'
 *   Str::snake('UserProfilePage');          // 'user_profile_page'
 *   Str::random(32);                        // 'aB3xQ…' (URL-safe)
 *   Str::mask('hello@example.com', '*', 3); // 'hel**@example.com'
 *   Str::contains('foobar', 'oba');         // true
 *   Str::startsWith('foobar', 'foo');       // true
 *   Str::endsWith('foobar', 'bar');         // true
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class Str
{
    // ── Case transformations ──────────────────────────────────────────────────

    /**
     * Convert a string to StudlyCase (UpperCamelCase).
     *
     *   Str::studly('user_profile_page')  → 'UserProfilePage'
     *   Str::studly('hello-world')        → 'HelloWorld'
     */
    public static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    /**
     * Convert a string to camelCase.
     *
     *   Str::camel('user_profile_page')  → 'userProfilePage'
     */
    public static function camel(string $value): string
    {
        return lcfirst(self::studly($value));
    }

    /**
     * Convert a StudlyCase or camelCase string to snake_case.
     *
     *   Str::snake('UserProfilePage')  → 'user_profile_page'
     *   Str::snake('helloWorld')       → 'hello_world'
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value)) ?? $value;
            $value = strtolower(
                preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value) ?? $value
            );
        }

        return $value;
    }

    /**
     * Convert a string to kebab-case.
     *
     *   Str::kebab('UserProfilePage')  → 'user-profile-page'
     */
    public static function kebab(string $value): string
    {
        return self::snake($value, '-');
    }

    /**
     * Convert a string to Title Case.
     *
     *   Str::title('the quick brown fox')  → 'The Quick Brown Fox'
     */
    public static function title(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    // ── URL / slug ────────────────────────────────────────────────────────────

    /**
     * Generate a URL-safe slug.
     *
     *   Str::slug('Hello, World!')     → 'hello-world'
     *   Str::slug('Héllo Wörld')       → 'hello-world'
     *   Str::slug('foo   bar', '_')    → 'foo_bar'
     */
    public static function slug(string $value, string $separator = '-'): string
    {
        // Transliterate accented characters to ASCII
        $value = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $value)
              ?? mb_strtolower($value);

        // Replace non-alphanumeric characters with the separator
        $value = preg_replace('/[^a-z0-9]+/i', $separator, $value) ?? $value;

        // Collapse multiple separators and trim
        $escaped = preg_quote($separator, '/');
        $value   = preg_replace('/(' . $escaped . ')+/', $separator, $value) ?? $value;

        return trim($value, $separator);
    }

    // ── Truncation ────────────────────────────────────────────────────────────

    /**
     * Limit a string to $limit characters, appending $end on overflow.
     *
     *   Str::truncate('Hello World', 8)         → 'Hello Wo…'
     *   Str::truncate('Hello World', 8, '...')  → 'Hello Wo...'
     */
    public static function truncate(string $value, int $limit, string $end = '…'): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit)) . $end;
    }

    /**
     * Limit a string to $words words.
     *
     *   Str::words('The quick brown fox', 3)  → 'The quick brown…'
     */
    public static function words(string $value, int $words = 100, string $end = '…'): string
    {
        preg_match('/^\s*+(?:\S+\s*+){1,' . $words . '}/u', $value, $matches);

        if (!isset($matches[0]) || mb_strlen($value) === mb_strlen($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]) . $end;
    }

    // ── Search & match ────────────────────────────────────────────────────────

    /**
     * Check whether $haystack contains $needle (case-sensitive).
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return str_contains($haystack, $needle);
    }

    /**
     * Check whether $haystack contains any of the given needles.
     *
     * @param list<string> $needles
     */
    public static function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    // ── Generation ────────────────────────────────────────────────────────────

    /**
     * Generate a cryptographically random URL-safe string of $length characters.
     *
     * Uses the charset: [A-Za-z0-9-_]
     */
    public static function random(int $length = 32): string
    {
        $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
        $max    = strlen($chars) - 1;
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }

        return $result;
    }

    /**
     * Generate a UUID v4.
     */
    public static function uuid(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    // ── Masking ───────────────────────────────────────────────────────────────

    /**
     * Mask a portion of a string with a repeated character.
     *
     * Useful for displaying partial email addresses or credit card numbers.
     *
     *   Str::mask('hello@example.com', '*', 3)   → 'hel**@example.com'
     *   Str::mask('4111111111111111', 'x', 0, 12) → 'xxxxxxxxxxxx1111'
     *
     * @param string $value      The string to mask.
     * @param string $char       The masking character.
     * @param int    $show_start Number of characters to reveal at the start.
     * @param int    $show_end   Number of characters to reveal at the end. 0 = mask remaining.
     */
    public static function mask(string $value, string $char = '*', int $show_start = 4, int $show_end = 0): string
    {
        $len = mb_strlen($value);

        if ($len <= $show_start + $show_end) {
            return $value;
        }

        $visible_start = mb_substr($value, 0, $show_start);
        $visible_end   = $show_end > 0 ? mb_substr($value, -$show_end) : '';
        $masked_len    = $len - $show_start - $show_end;

        return $visible_start . str_repeat(mb_substr($char, 0, 1), $masked_len) . $visible_end;
    }

    // ── Utilities ─────────────────────────────────────────────────────────────

    /**
     * Pad a string to $length using $pad from both, left or right.
     */
    public static function pad(string $value, int $length, string $pad = ' ', int $type = STR_PAD_RIGHT): string
    {
        return str_pad($value, $length, $pad, $type);
    }

    /**
     * Repeat a string $times times.
     */
    public static function repeat(string $value, int $times): string
    {
        return str_repeat($value, $times);
    }

    /**
     * Swap keys with values for a simple string-level find-and-replace.
     *
     *   Str::swap(['foo' => 'bar', 'baz' => 'qux'], 'foo and baz') → 'bar and qux'
     *
     * @param array<string, string> $map
     */
    public static function swap(array $map, string $subject): string
    {
        return strtr($subject, $map);
    }

    /**
     * Check whether the string is a valid JSON value.
     */
    public static function isJson(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Return the number of bytes in a string (not characters).
     */
    public static function byteSize(string $value): int
    {
        return strlen($value);
    }

    /**
     * Return the character length of a string (UTF-8 aware).
     */
    public static function length(string $value): int
    {
        return mb_strlen($value);
    }

    /**
     * Extract the portion of a string between two delimiters.
     *
     *   Str::between('<tag>hello</tag>', '<tag>', '</tag>')  → 'hello'
     */
    public static function between(string $value, string $from, string $to): string
    {
        $start = mb_strpos($value, $from);

        if ($start === false) {
            return $value;
        }

        $start += mb_strlen($from);
        $end    = mb_strpos($value, $to, $start);

        if ($end === false) {
            return mb_substr($value, $start);
        }

        return mb_substr($value, $start, $end - $start);
    }

    /**
     * Format a byte count as a human-readable string.
     *
     *   Str::formatBytes(1536)    → '1.5 KB'
     *   Str::formatBytes(1048576) → '1 MB'
     */
    public static function formatBytes(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $i     = 0;
        $n     = (float) $bytes;

        while ($n >= 1024 && $i < count($units) - 1) {
            $n /= 1024;
            $i++;
        }

        return round($n, $precision) . ' ' . $units[$i];
    }
}
