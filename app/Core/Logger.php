<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Logger — Simple file-based logger with PSR-3-inspired levels and channels.
 *
 * Log files are written to /storage/logs/ by default and rotate daily:
 *   storage/logs/app-2025-06-10.log
 *   storage/logs/db-2025-06-10.log
 *
 * Usage:
 *   Logger::info('User logged in', ['user_id' => 42]);
 *   Logger::error('Payment failed', ['order' => $id, 'reason' => $msg]);
 *   Logger::channel('db')->debug('Query executed', ['sql' => $sql, 'ms' => 12]);
 *
 *   // In routes or controllers:
 *   log_info('Cache miss', ['key' => $cacheKey]);
 *   log_error('Stripe webhook failed', ['payload' => $body]);
 *
 * Log line format:
 *   [2025-06-10 14:32:01] app.INFO: User logged in {"user_id":42}
 *
 * Configuration via .env:
 *   LOG_CHANNEL=app        (default channel name)
 *   LOG_LEVEL=debug        (minimum level to record; default: debug)
 *   LOG_PATH=              (override directory; default: BASE_PATH/storage/logs)
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class Logger
{
    // PSR-3 levels (lowest → highest)
    public const DEBUG     = 'debug';
    public const INFO      = 'info';
    public const NOTICE    = 'notice';
    public const WARNING   = 'warning';
    public const ERROR     = 'error';
    public const CRITICAL  = 'critical';
    public const ALERT     = 'alert';
    public const EMERGENCY = 'emergency';

    /** @var array<string, int> Level weights for min-level filtering */
    private const WEIGHTS = [
        self::DEBUG     => 0,
        self::INFO      => 1,
        self::NOTICE    => 2,
        self::WARNING   => 3,
        self::ERROR     => 4,
        self::CRITICAL  => 5,
        self::ALERT     => 6,
        self::EMERGENCY => 7,
    ];

    private static ?Logger $default = null;

    /** @var array<string, Logger> Named channel instances */
    private static array $channels = [];

    private readonly string $channelName;
    private readonly string $logDir;
    private readonly int    $minWeight;

    // ── Factory ───────────────────────────────────────────────────────────────

    private function __construct(string $channel, string $logDir, string $minLevel)
    {
        $this->channelName = $channel;
        $this->logDir      = rtrim($logDir, '/');
        $this->minWeight   = self::WEIGHTS[$minLevel] ?? 0;
    }

    /**
     * Return (or create) a named channel instance.
     *
     * Logger::channel('payments')->error('Charge declined', ['id' => $id]);
     */
    public static function channel(string $name = ''): self
    {
        if ($name === '') {
            $name = Config::get('LOG_CHANNEL', 'app');
        }

        if (!isset(self::$channels[$name])) {
            $logDir   = self::resolveLogDir();
            $minLevel = strtolower(Config::get('LOG_LEVEL', self::DEBUG));
            self::$channels[$name] = new self($name, $logDir, $minLevel);
        }

        return self::$channels[$name];
    }

    // ── PSR-3 level methods (static — delegate to default channel) ────────────

    public static function debug(string $message, array $context = []): void
    {
        self::defaultChannel()->log(self::DEBUG, $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::defaultChannel()->log(self::INFO, $message, $context);
    }

    public static function notice(string $message, array $context = []): void
    {
        self::defaultChannel()->log(self::NOTICE, $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::defaultChannel()->log(self::WARNING, $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::defaultChannel()->log(self::ERROR, $message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::defaultChannel()->log(self::CRITICAL, $message, $context);
    }

    public static function alert(string $message, array $context = []): void
    {
        self::defaultChannel()->log(self::ALERT, $message, $context);
    }

    public static function emergency(string $message, array $context = []): void
    {
        self::defaultChannel()->log(self::EMERGENCY, $message, $context);
    }

    // ── Instance-level log ────────────────────────────────────────────────────

    /**
     * Write a log entry on this channel instance.
     *
     * @param string               $level   One of the Logger::* constants.
     * @param string               $message Human-readable message.
     * @param array<string, mixed> $context Arbitrary structured data.
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $weight = self::WEIGHTS[$level] ?? 0;

        if ($weight < $this->minWeight) {
            return;
        }

        $this->ensureLogDir();

        $date      = date('Y-m-d');
        $timestamp = date('Y-m-d H:i:s');
        $file      = "{$this->logDir}/{$this->channelName}-{$date}.log";
        $ctx       = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $levelUp   = strtoupper($level);
        $line      = "[{$timestamp}] {$this->channelName}.{$levelUp}: {$message}{$ctx}" . PHP_EOL;

        // Append-only; tolerate race conditions in concurrent environments
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    // ── Convenience instance shorthand ────────────────────────────────────────

    public function debug(string $message, array $context = []): void    { $this->log(self::DEBUG,     $message, $context); }
    public function info(string $message, array $context = []): void     { $this->log(self::INFO,      $message, $context); }
    public function notice(string $message, array $context = []): void   { $this->log(self::NOTICE,    $message, $context); }
    public function warning(string $message, array $context = []): void  { $this->log(self::WARNING,   $message, $context); }
    public function error(string $message, array $context = []): void    { $this->log(self::ERROR,     $message, $context); }
    public function critical(string $message, array $context = []): void { $this->log(self::CRITICAL,  $message, $context); }
    public function alert(string $message, array $context = []): void    { $this->log(self::ALERT,     $message, $context); }
    public function emergency(string $message, array $context = []): void{ $this->log(self::EMERGENCY, $message, $context); }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function defaultChannel(): self
    {
        return self::channel('');
    }

    private static function resolveLogDir(): string
    {
        $configured = Config::get('LOG_PATH', '');

        if ($configured !== '') {
            return $configured;
        }

        return defined('BASE_PATH')
            ? BASE_PATH . '/storage/logs'
            : dirname(__DIR__, 3) . '/storage/logs';
    }

    private function ensureLogDir(): void
    {
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
}
