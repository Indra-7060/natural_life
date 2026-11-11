<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Access control, customers, settings, media, navigation, SEO.
 * The project targets MySQL/MariaDB only, so tables are declared in SQL for exact
 * control over foreign keys, indexes and column types.
 */
class CreateCoreTables extends Migration
{
    private string $opts = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function up(): void
    {
        $q = fn (string $sql) => $this->db->query($sql . ' ' . $this->opts);

        $q('CREATE TABLE roles (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(60) NOT NULL,
            slug VARCHAR(60) NOT NULL,
            description VARCHAR(255) NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_roles_slug (slug)
        )');

        $q('CREATE TABLE permissions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            module VARCHAR(60) NOT NULL,
            label VARCHAR(120) NOT NULL,
            UNIQUE KEY uq_permissions_module (module)
        )');

        $q('CREATE TABLE role_permissions (
            role_id INT UNSIGNED NOT NULL,
            permission_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (role_id, permission_id),
            CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
            CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        )');

        $q('CREATE TABLE admin_users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            role_id INT UNSIGNED NOT NULL,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            must_change_password TINYINT(1) NOT NULL DEFAULT 0,
            last_login_at DATETIME NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_admin_email (email),
            KEY idx_admin_role (role_id),
            CONSTRAINT fk_admin_role FOREIGN KEY (role_id) REFERENCES roles(id)
        )');

        $q('CREATE TABLE customers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(80) NOT NULL,
            last_name VARCHAR(80) NOT NULL DEFAULT "",
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(20) NULL,
            password_hash VARCHAR(255) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            last_login_at DATETIME NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_customers_email (email)
        )');

        $q('CREATE TABLE customer_addresses (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer_id INT UNSIGNED NOT NULL,
            label VARCHAR(40) NOT NULL DEFAULT "Home",
            full_name VARCHAR(160) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            line1 VARCHAR(190) NOT NULL,
            line2 VARCHAR(190) NULL,
            city VARCHAR(90) NOT NULL,
            state VARCHAR(90) NOT NULL,
            postcode VARCHAR(12) NOT NULL,
            country VARCHAR(60) NOT NULL DEFAULT "India",
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            KEY idx_addr_customer (customer_id),
            CONSTRAINT fk_addr_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        )');

        $q('CREATE TABLE password_resets (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_type ENUM("customer","admin") NOT NULL DEFAULT "customer",
            email VARCHAR(190) NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at DATETIME NULL,
            KEY idx_reset_token (token_hash),
            KEY idx_reset_email (email)
        )');

        $q('CREATE TABLE settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `group` VARCHAR(40) NOT NULL DEFAULT "general",
            `key` VARCHAR(80) NOT NULL,
            `value` TEXT NULL,
            label VARCHAR(120) NOT NULL DEFAULT "",
            type VARCHAR(20) NOT NULL DEFAULT "text",
            help VARCHAR(255) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            updated_at DATETIME NULL,
            UNIQUE KEY uq_settings_key (`key`),
            KEY idx_settings_group (`group`)
        )');

        $q('CREATE TABLE media (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            folder VARCHAR(60) NOT NULL DEFAULT "general",
            file_name VARCHAR(190) NOT NULL,
            path VARCHAR(255) NOT NULL,
            mime VARCHAR(80) NOT NULL,
            size_bytes INT UNSIGNED NOT NULL DEFAULT 0,
            width INT UNSIGNED NULL,
            height INT UNSIGNED NULL,
            alt_text VARCHAR(255) NULL,
            source_url VARCHAR(255) NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_media_path (path),
            KEY idx_media_folder (folder)
        )');

        $q('CREATE TABLE menus (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(80) NOT NULL,
            location VARCHAR(40) NOT NULL,
            UNIQUE KEY uq_menus_location (location)
        )');

        $q('CREATE TABLE menu_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            menu_id INT UNSIGNED NOT NULL,
            parent_id INT UNSIGNED NULL,
            label VARCHAR(120) NOT NULL,
            url VARCHAR(255) NOT NULL DEFAULT "#",
            group_label VARCHAR(80) NULL,
            description VARCHAR(160) NULL,
            open_new_tab TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            KEY idx_mi_menu (menu_id, sort_order),
            KEY idx_mi_parent (parent_id),
            CONSTRAINT fk_mi_menu FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
            CONSTRAINT fk_mi_parent FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE
        )');

        $q('CREATE TABLE seo_meta (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            route_key VARCHAR(80) NOT NULL,
            label VARCHAR(120) NOT NULL,
            meta_title VARCHAR(190) NULL,
            meta_description VARCHAR(320) NULL,
            og_image VARCHAR(255) NULL,
            noindex TINYINT(1) NOT NULL DEFAULT 0,
            updated_at DATETIME NULL,
            UNIQUE KEY uq_seo_route (route_key)
        )');

        $q('CREATE TABLE redirects (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            from_path VARCHAR(190) NOT NULL,
            to_path VARCHAR(255) NOT NULL,
            status_code SMALLINT NOT NULL DEFAULT 301,
            hits INT UNSIGNED NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            note VARCHAR(190) NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_redirect_from (from_path)
        )');
    }

    public function down(): void
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        foreach (['redirects', 'seo_meta', 'menu_items', 'menus', 'media', 'settings', 'password_resets', 'customer_addresses', 'customers', 'admin_users', 'role_permissions', 'permissions', 'roles'] as $t) {
            $this->forge->dropTable($t, true);
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
