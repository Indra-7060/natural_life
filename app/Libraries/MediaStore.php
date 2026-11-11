<?php

namespace App\Libraries;

/**
 * Keeps uploaded media in the `media_files` table. Used on hosts with a read-only
 * disk (Vercel), where a file written to public/uploads would vanish after the request.
 * Files are split into pieces so they pass through databases with a small packet limit.
 */
class MediaStore
{
    private const TABLE = 'media_files';
    private const PIECE = 262144; // 256 KB

    /** @var array<string, true>|null paths known to be stored, loaded once per request */
    private static ?array $index = null;

    /** Saves a local file under the given library path, e.g. "original/products/photo.jpg". */
    public function put(string $path, string $localFile, string $mime): void
    {
        $db = db_connect();
        $db->table(self::TABLE)->where('path', $path)->delete();

        $handle = fopen($localFile, 'rb');
        $seq    = 0;
        while (! feof($handle)) {
            $piece = fread($handle, self::PIECE);
            if ($piece === false || ($piece === '' && $seq > 0)) {
                break;
            }
            $db->query(
                'INSERT INTO ' . self::TABLE . ' (path, seq, mime, data, created_at) VALUES (?, ?, ?, UNHEX(?), ?)',
                [$path, $seq++, $mime, bin2hex($piece), date('Y-m-d H:i:s')],
            );
        }
        fclose($handle);
        self::$index = null;
    }

    /** Returns ['mime' => string, 'data' => string] or null when the path is not stored. */
    public function get(string $path): ?array
    {
        $rows = db_connect()->table(self::TABLE)->select('mime, data')->where('path', $path)->orderBy('seq')->get()->getResultArray();
        if ($rows === []) {
            return null;
        }

        return ['mime' => $rows[0]['mime'], 'data' => implode('', array_column($rows, 'data'))];
    }

    public function delete(string $path): void
    {
        db_connect()->table(self::TABLE)->where('path', $path)->delete();
        self::$index = null;
    }

    public function has(string $path): bool
    {
        if (self::$index === null) {
            self::$index = [];
            foreach (db_connect()->table(self::TABLE)->select('path')->where('seq', 0)->get()->getResultArray() as $row) {
                self::$index[$row['path']] = true;
            }
        }

        return isset(self::$index[$path]);
    }
}
