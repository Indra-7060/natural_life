<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Removes the full-text index on products. The search uses LIKE and never used this index,
 * and MySQL-compatible cloud databases such as TiDB can refuse to create it.
 */
class DropProductFulltextIndex extends Migration
{
    public function up(): void
    {
        $exists = $this->db->query("SHOW INDEX FROM products WHERE Key_name = 'ft_products'")->getNumRows() > 0;
        if ($exists) {
            $this->db->query('ALTER TABLE products DROP INDEX ft_products');
        }
    }

    public function down(): void
    {
        // intentionally not restored
    }
}
