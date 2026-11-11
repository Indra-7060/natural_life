<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tables used when the site runs on a host with a read-only disk (Vercel):
 * sessions, cache and uploaded media are kept in the database instead of in files.
 * They stay empty on ordinary hosting.
 */
class CreateServerlessTables extends Migration
{
    private string $opts = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function up(): void
    {
        $q = fn (string $sql) => $this->db->query($sql . ' ' . $this->opts);

        // layout required by CodeIgniter's database session handler
        $q('CREATE TABLE ci_sessions (
            id VARCHAR(128) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            data BLOB NOT NULL,
            PRIMARY KEY (id),
            KEY ix_ci_sessions_timestamp (`timestamp`)
        )');

        $q('CREATE TABLE cache_store (
            cache_key VARCHAR(190) NOT NULL,
            payload MEDIUMTEXT NOT NULL,
            expires_at INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (cache_key),
            KEY ix_cache_store_expires (expires_at)
        )');

        // uploaded files, split into small pieces so no single query is large
        $q('CREATE TABLE media_files (
            path VARCHAR(190) NOT NULL,
            seq SMALLINT UNSIGNED NOT NULL,
            mime VARCHAR(80) NOT NULL,
            data MEDIUMBLOB NOT NULL,
            created_at DATETIME NULL,
            PRIMARY KEY (path, seq)
        )');
    }

    public function down(): void
    {
        foreach (['media_files', 'cache_store', 'ci_sessions'] as $table) {
            $this->db->query('DROP TABLE IF EXISTS ' . $table);
        }
    }
}
