<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['group', 'key', 'value', 'label', 'type', 'help', 'sort_order'];
    protected $useTimestamps = true;
    protected $createdField  = '';

    private static ?array $cache = null;

    /** All settings as key => value, loaded once per request and cached for 10 minutes. */
    public function allKeyed(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $data = cache('site_settings');
        if (! is_array($data)) {
            $data = [];

            try {
                foreach ($this->select('key, value')->findAll() as $row) {
                    $data[$row['key']] = $row['value'];
                }
                cache()->save('site_settings', $data, 600);
            } catch (\Throwable $e) {
                $data = [];
            }
        }

        return self::$cache = $data;
    }

    public static function flush(): void
    {
        self::$cache = null;
        cache()->delete('site_settings');
    }
}
