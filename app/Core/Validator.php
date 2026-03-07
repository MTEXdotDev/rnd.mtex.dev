<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Validator — Rule-based input validation engine.
 *
 * Rules are declared as pipe-separated strings per field:
 *
 *   $v = Validator::make(Request::all(), [
 *       'name'  => 'required|min_length:2|max_length:80',
 *       'email' => 'required|email',
 *       'age'   => 'required|numeric|min:18|max:120',
 *       'role'  => 'required|in:admin,editor,viewer',
 *       'slug'  => 'required|alpha_dash',
 *       'site'  => 'url',
 *       'code'  => 'regex:/^[A-Z]{3}[0-9]{3}$/',
 *   ]);
 *
 *   if ($v->fails()) {
 *       Session::flash('errors', $v->errors());
 *       Session::flash('old',    $v->old());
 *       redirect('/form');
 *   }
 *
 *   $data = $v->validated();   // only the declared fields, safe to INSERT
 *
 * Supported rules:
 *   required          Field must be present and non-empty string.
 *   nullable          Field may be absent or empty (disables required).
 *   string            Must be a string value.
 *   numeric           Must be numeric (int or float).
 *   integer           Must be an integer value.
 *   boolean           Must be "true", "false", "1", "0", true, or false.
 *   email             Must be a valid e-mail address.
 *   url               Must be a valid URL (http or https).
 *   alpha             Letters only [a-zA-Z].
 *   alpha_num         Letters and digits only.
 *   alpha_dash        Letters, digits, hyphens and underscores.
 *   min:N             Numeric value must be >= N.
 *   max:N             Numeric value must be <= N.
 *   min_length:N      String length must be >= N characters.
 *   max_length:N      String length must be <= N characters.
 *   in:a,b,c          Value must be one of the listed options.
 *   not_in:a,b        Value must NOT be one of the listed options.
 *   confirmed          A matching "{field}_confirmation" field must exist.
 *   regex:/pattern/   Value must match the regex.
 *   date              Must be parseable by strtotime().
 *   accepted          Must be "yes", "on", "1", or true.
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class Validator
{
    /** @var array<string, list<string>> field → list of error messages */
    private array $errors = [];

    /** @var array<string, mixed> Validated (safe) values for declared fields */
    private array $validated = [];

    // ── Factory ───────────────────────────────────────────────────────────────

    /**
     * @param array<string, mixed>  $data  Input data (e.g. from Request::all()).
     * @param array<string, string> $rules Field → rule string map.
     * @param array<string, string> $messages Optional custom error messages.
     *                                         Key format: "field.rule" e.g. "email.required".
     */
    public static function make(
        array $data,
        array $rules,
        array $messages = [],
    ): self {
        $validator = new self();
        $validator->run($data, $rules, $messages);
        return $validator;
    }

    // ── Result accessors ──────────────────────────────────────────────────────

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Return the first error message for a field, or null.
     */
    public function error(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Return a flat list of all error messages.
     *
     * @return list<string>
     */
    public function allErrors(): array
    {
        return array_merge(...array_values($this->errors));
    }

    /**
     * Return only the validated (declared) fields.
     * Throws if validation has not passed.
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        if ($this->fails()) {
            throw new \RuntimeException(
                'Cannot retrieve validated data — validation failed with errors: '
                . implode(', ', $this->allErrors())
            );
        }

        return $this->validated;
    }

    /**
     * Return all input data as-is (for re-populating forms on failure).
     *
     * @return array<string, mixed>
     */
    public function old(): array
    {
        return $this->validated; // validated holds all declared-field values
    }

    // ── Core engine ───────────────────────────────────────────────────────────

    private function run(array $data, array $rules, array $messages): void
    {
        foreach ($rules as $field => $ruleString) {
            $ruleList = array_map('trim', explode('|', $ruleString));
            $value    = $data[$field] ?? null;

            // Store value for old() / validated()
            $this->validated[$field] = $value;

            $nullable = in_array('nullable', $ruleList, true);
            $isEmpty  = $value === null || $value === '' || $value === [];

            foreach ($ruleList as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }

                // Skip all other rules for empty nullable fields
                if ($nullable && $isEmpty && $rule !== 'required') {
                    continue;
                }

                [$name, $param] = $this->parseRule($rule);

                $error = $this->applyRule($name, $param, $field, $value, $data);

                if ($error !== null) {
                    $key = "{$field}.{$name}";
                    $this->addError($field, $messages[$key] ?? $error);
                    break; // Stop at first error per field (like Laravel)
                }
            }
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseRule(string $rule): array
    {
        if (str_contains($rule, ':')) {
            $pos = strpos($rule, ':');
            return [substr($rule, 0, $pos), substr($rule, $pos + 1)];
        }

        return [$rule, ''];
    }

    /**
     * Apply a single rule. Returns an error message string on failure, null on pass.
     *
     * @param array<string, mixed> $allData Full input (needed for "confirmed" rule).
     */
    private function applyRule(
        string $name,
        string $param,
        string $field,
        mixed  $value,
        array  $allData,
    ): ?string {
        $label   = ucfirst(str_replace('_', ' ', $field));
        $isEmpty = $value === null || $value === '';

        return match ($name) {

            'required' => $isEmpty
                ? "{$label} is required."
                : null,

            'string' => !$isEmpty && !is_string($value)
                ? "{$label} must be a string."
                : null,

            'numeric' => !$isEmpty && !is_numeric($value)
                ? "{$label} must be a number."
                : null,

            'integer' => !$isEmpty && filter_var($value, FILTER_VALIDATE_INT) === false
                ? "{$label} must be an integer."
                : null,

            'boolean' => !$isEmpty && !in_array(
                    strtolower((string) $value), ['true', 'false', '1', '0'], true
                )
                ? "{$label} must be true or false."
                : null,

            'email' => !$isEmpty && !filter_var($value, FILTER_VALIDATE_EMAIL)
                ? "{$label} must be a valid email address."
                : null,

            'url' => !$isEmpty && !filter_var($value, FILTER_VALIDATE_URL)
                ? "{$label} must be a valid URL."
                : null,

            'alpha' => !$isEmpty && !ctype_alpha((string) $value)
                ? "{$label} may only contain letters."
                : null,

            'alpha_num' => !$isEmpty && !ctype_alnum((string) $value)
                ? "{$label} may only contain letters and numbers."
                : null,

            'alpha_dash' => !$isEmpty && !preg_match('/^[a-zA-Z0-9_-]+$/', (string) $value)
                ? "{$label} may only contain letters, numbers, dashes and underscores."
                : null,

            'min' => !$isEmpty && is_numeric($value) && (float) $value < (float) $param
                ? "{$label} must be at least {$param}."
                : null,

            'max' => !$isEmpty && is_numeric($value) && (float) $value > (float) $param
                ? "{$label} must not exceed {$param}."
                : null,

            'min_length' => !$isEmpty && mb_strlen((string) $value) < (int) $param
                ? "{$label} must be at least {$param} characters."
                : null,

            'max_length' => !$isEmpty && mb_strlen((string) $value) > (int) $param
                ? "{$label} must not exceed {$param} characters."
                : null,

            'in' => !$isEmpty && !in_array((string) $value, explode(',', $param), true)
                ? "{$label} must be one of: " . str_replace(',', ', ', $param) . "."
                : null,

            'not_in' => !$isEmpty && in_array((string) $value, explode(',', $param), true)
                ? "{$label} contains an invalid value."
                : null,

            'confirmed' => (string) $value !== (string) ($allData["{$field}_confirmation"] ?? '')
                ? "{$label} confirmation does not match."
                : null,

            'regex' => !$isEmpty && !preg_match($param, (string) $value)
                ? "{$label} format is invalid."
                : null,

            'date' => !$isEmpty && strtotime((string) $value) === false
                ? "{$label} must be a valid date."
                : null,

            'accepted' => !in_array(
                    strtolower((string) $value), ['yes', 'on', '1', 'true'], true
                )
                ? "{$label} must be accepted."
                : null,

            default => null,
        };
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
