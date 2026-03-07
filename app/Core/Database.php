<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Database — Multi-driver PDO wrapper (MySQL / SQLite).
 *
 * Driver is selected via the DB_DRIVER environment variable:
 *   DB_DRIVER=mysql   → connects to a MySQL / MariaDB server (default)
 *   DB_DRIVER=sqlite  → opens (or creates) a local SQLite database file
 *
 * All CRUD helpers use prepared statements for SQL-injection safety.
 * Identifier quoting adapts automatically to the active driver.
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class Database
{
    private static ?Database $instance = null;

    private readonly PDO    $pdo;
    private readonly string $driver;

    // ── Constructor ───────────────────────────────────────────────────────────

    private function __construct()
    {
        $this->driver = strtolower(Config::get('DB_DRIVER', 'mysql'));

        $this->pdo = match ($this->driver) {
            'sqlite' => $this->connectSqlite(),
            'mysql'  => $this->connectMysql(),
            default  => throw new RuntimeException(
                "Unsupported DB_DRIVER [{$this->driver}]. Use 'mysql' or 'sqlite'."
            ),
        };
    }

    // ── Driver connections ────────────────────────────────────────────────────

    private function connectMysql(): PDO
    {
        $host    = Config::get('DB_HOST',    'localhost');
        $port    = Config::get('DB_PORT',    '3306');
        $name    = Config::get('DB_NAME',    '');
        $user    = Config::get('DB_USER',    'root');
        $pass    = Config::get('DB_PASS',    '');
        $charset = Config::get('DB_CHARSET', 'utf8mb4');

        if ($name === '') {
            throw new RuntimeException('DB_NAME must be set for the MySQL driver.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        return $this->buildPdo($dsn, $user, $pass);
    }

    private function connectSqlite(): PDO
    {
        $path = Config::get('DB_PATH', '');

        if ($path === '') {
            $path = defined('BASE_PATH')
                ? BASE_PATH . '/database/database.sqlite'
                : dirname(__DIR__, 3) . '/database/database.sqlite';
        }

        if ($path !== ':memory:') {
            $dir = dirname($path);

            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new RuntimeException("Could not create SQLite directory: {$dir}");
            }
        }

        $pdo = $this->buildPdo("sqlite:{$path}", '', '');

        // Recommended SQLite pragmas for performance and safety
        $pdo->exec('PRAGMA journal_mode = WAL;');
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $pdo->exec('PRAGMA synchronous  = NORMAL;');

        return $pdo;
    }

    private function buildPdo(string $dsn, string $user, string $pass): PDO
    {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            return new PDO($dsn, $user ?: null, $pass ?: null, $options);
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Database connection failed ({$this->driver}): " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    // ── Singleton ─────────────────────────────────────────────────────────────

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Reset the singleton — useful in tests when switching drivers.
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    private function __clone(): void {}

    // ── Driver info ───────────────────────────────────────────────────────────

    public function getDriver(): string { return $this->driver; }
    public function isSqlite(): bool    { return $this->driver === 'sqlite'; }
    public function isMysql(): bool     { return $this->driver === 'mysql';  }

    // ── Query helpers ─────────────────────────────────────────────────────────

    /**
     * Execute a parameterised SQL query and return the prepared statement.
     *
     * @param  string               $sql
     * @param  array<string, mixed> $params
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // ── SELECT ────────────────────────────────────────────────────────────────

    /**
     * Fetch all matching rows.
     *
     * @param  string               $sql
     * @param  array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function select(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Fetch a single row, or null.
     *
     * @param  string               $sql
     * @param  array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    public function selectOne(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Fetch a single scalar value (first column of first row).
     */
    public function scalar(string $sql, array $params = []): mixed
    {
        $row = $this->selectOne($sql, $params);
        return $row ? array_values($row)[0] : null;
    }

    // ── INSERT ────────────────────────────────────────────────────────────────

    /**
     * Insert a row and return the last insert ID as a string.
     *
     * @param  string               $table Table name — must NOT be user-supplied.
     * @param  array<string, mixed> $data  Column → value map.
     */
    public function insert(string $table, array $data): string
    {
        $t       = $this->qi($table);
        $columns = implode(', ', array_map($this->qi(...), array_keys($data)));
        $holders = implode(', ', array_map(static fn ($k) => ":{$k}", array_keys($data)));

        $this->query("INSERT INTO {$t} ({$columns}) VALUES ({$holders})", $data);

        return $this->pdo->lastInsertId();
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    /**
     * Update rows matching all $where conditions (ANDed).
     *
     * @param  string               $table
     * @param  array<string, mixed> $data   Columns to set.
     * @param  array<string, mixed> $where  Conditions.
     * @return int Affected row count.
     */
    public function update(string $table, array $data, array $where): int
    {
        $t = $this->qi($table);

        $setClauses   = array_map(fn ($k) => $this->qi($k) . " = :set_{$k}",   array_keys($data));
        $whereClauses = array_map(fn ($k) => $this->qi($k) . " = :where_{$k}", array_keys($where));

        $params = [];
        foreach ($data  as $k => $v) { $params["set_{$k}"]   = $v; }
        foreach ($where as $k => $v) { $params["where_{$k}"] = $v; }

        $sql = "UPDATE {$t} SET " . implode(', ', $setClauses)
             . ' WHERE '          . implode(' AND ', $whereClauses);

        return $this->query($sql, $params)->rowCount();
    }

    // ── DELETE ────────────────────────────────────────────────────────────────

    /**
     * Delete rows matching all $where conditions (ANDed).
     *
     * @param  string               $table
     * @param  array<string, mixed> $where Conditions.
     * @return int Deleted row count.
     */
    public function delete(string $table, array $where): int
    {
        $t = $this->qi($table);

        $whereClauses = array_map(
            fn ($k) => $this->qi($k) . " = :{$k}",
            array_keys($where)
        );

        $sql = "DELETE FROM {$t} WHERE " . implode(' AND ', $whereClauses);

        return $this->query($sql, $where)->rowCount();
    }

    // ── Schema helpers ────────────────────────────────────────────────────────

    /**
     * Check whether a table exists. Works on both MySQL and SQLite.
     */
    public function tableExists(string $table): bool
    {
        if ($this->isSqlite()) {
            return $this->selectOne(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name = :n",
                ['n' => $table]
            ) !== null;
        }

        return $this->selectOne(
            'SELECT TABLE_NAME FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t LIMIT 1',
            ['t' => $table]
        ) !== null;
    }

    /**
     * Execute a raw DDL statement (CREATE TABLE, ALTER TABLE, etc.).
     */
    public function statement(string $sql): bool
    {
        return $this->pdo->exec($sql) !== false;
    }

    // ── Transactions ──────────────────────────────────────────────────────────

    public function beginTransaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void           { $this->pdo->commit();           }
    public function rollback(): void         { $this->pdo->rollBack();         }

    /**
     * Execute a callable inside a transaction; auto-rollback on exception.
     *
     * @template T
     * @param  callable(): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback();
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    // ── Utilities ─────────────────────────────────────────────────────────────

    public function getPdo(): PDO { return $this->pdo; }

    // ── Identifier quoting ────────────────────────────────────────────────────

    /**
     * Safely quote a table / column identifier for the active driver.
     *
     * MySQL  → `identifier`   (backticks)
     * SQLite → "identifier"   (double-quotes, standard SQL)
     */
    private function qi(string $identifier): string
    {
        if (!preg_match('/^\w+$/', $identifier)) {
            throw new RuntimeException("Invalid SQL identifier: {$identifier}");
        }

        return $this->isSqlite()
            ? '"' . $identifier . '"'
            : '`' . $identifier . '`';
    }
}
