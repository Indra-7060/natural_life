<?php

namespace App\Libraries;

use CodeIgniter\Cache\Handlers\BaseHandler;
use CodeIgniter\Database\BaseConnection;
use Config\Cache;
use Throwable;

/**
 * Cache kept in the `cache_store` table. Used on hosts with a read-only disk (Vercel),
 * where every request may run on a different machine: a file cache there would let
 * one machine keep serving old settings after an admin change, and would make the
 * sign-in attempt limits count per machine instead of per visitor.
 */
class DatabaseCacheHandler extends BaseHandler
{
    private const TABLE = 'cache_store';

    private ?BaseConnection $db = null;

    public function __construct(Cache $config)
    {
        $this->prefix = $config->prefix;
    }

    public function initialize(): void
    {
        $this->db = db_connect();
    }

    private function db(): BaseConnection
    {
        return $this->db ??= db_connect();
    }

    public function get(string $key): mixed
    {
        $row = $this->row($key);

        return $row === null ? null : unserialize($row['payload']);
    }

    public function save(string $key, mixed $value, int $ttl = 60): bool
    {
        $key = static::validateKey($key, $this->prefix);

        try {
            return (bool) $this->db()->query(
                'REPLACE INTO ' . self::TABLE . ' (cache_key, payload, expires_at) VALUES (?, ?, ?)',
                [$key, serialize($value), $ttl > 0 ? time() + $ttl : 0],
            );
        } catch (Throwable $e) {
            return false;
        }
    }

    public function delete(string $key): bool
    {
        $key = static::validateKey($key, $this->prefix);
        $this->db()->table(self::TABLE)->where('cache_key', $key)->delete();

        return $this->db()->affectedRows() > 0;
    }

    public function deleteMatching(string $pattern): int
    {
        $like = strtr($this->prefix . $pattern, ['\\' => '\\\\', '%' => '\\%', '_' => '\\_', '*' => '%']);
        $this->db()->query('DELETE FROM ' . self::TABLE . ' WHERE cache_key LIKE ?', [$like]);

        return $this->db()->affectedRows();
    }

    public function increment(string $key, int $offset = 1): bool|int
    {
        $row = $this->row($key);
        if ($row === null) {
            return false;
        }
        $value = unserialize($row['payload']);
        if (! is_int($value)) {
            return false;
        }
        $value += $offset;
        $this->db()->table(self::TABLE)->where('cache_key', $row['cache_key'])->update(['payload' => serialize($value)]);

        return $value;
    }

    public function decrement(string $key, int $offset = 1): bool|int
    {
        return $this->increment($key, -$offset);
    }

    public function clean(): bool
    {
        $this->db()->table(self::TABLE)->emptyTable();

        return true;
    }

    public function getCacheInfo(): array|false|object|null
    {
        return ['entries' => $this->db()->table(self::TABLE)->countAllResults()];
    }

    public function getMetaData(string $key): ?array
    {
        $row = $this->row($key);
        if ($row === null) {
            return null;
        }

        return ['expire' => (int) $row['expires_at'] ?: null, 'mtime' => null, 'data' => unserialize($row['payload'])];
    }

    public function isSupported(): bool
    {
        return true;
    }

    /** The stored row for a key, or null when missing or expired (expired rows are removed). */
    private function row(string $key): ?array
    {
        $key = static::validateKey($key, $this->prefix);

        try {
            $row = $this->db()->table(self::TABLE)->where('cache_key', $key)->get()->getRowArray();
        } catch (Throwable $e) {
            return null;
        }
        if ($row === null) {
            return null;
        }
        if ((int) $row['expires_at'] !== 0 && (int) $row['expires_at'] < time()) {
            $this->db()->table(self::TABLE)->where('cache_key', $key)->delete();
            // one request in twenty also sweeps other expired rows
            if (random_int(1, 20) === 1) {
                $this->db()->query('DELETE FROM ' . self::TABLE . ' WHERE expires_at <> 0 AND expires_at < ?', [time()]);
            }

            return null;
        }

        return $row;
    }
}
