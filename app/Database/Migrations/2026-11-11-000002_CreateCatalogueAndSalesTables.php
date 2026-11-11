<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Catalogue (products, variations, images, attributes, stock) and sales (carts, orders, payments). */
class CreateCatalogueAndSalesTables extends Migration
{
    private string $opts = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function up(): void
    {
        $q = fn (string $sql) => $this->db->query($sql . ' ' . $this->opts);

        $q('CREATE TABLE product_categories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            parent_id INT UNSIGNED NULL,
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(140) NOT NULL,
            description TEXT NULL,
            image VARCHAR(255) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            meta_title VARCHAR(190) NULL,
            meta_description VARCHAR(320) NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_pcat_slug (slug),
            KEY idx_pcat_parent (parent_id),
            CONSTRAINT fk_pcat_parent FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
        )');

        $q('CREATE TABLE products (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id INT UNSIGNED NULL,
            name VARCHAR(190) NOT NULL,
            slug VARCHAR(220) NOT NULL,
            sku VARCHAR(60) NULL,
            type ENUM("simple","variable") NOT NULL DEFAULT "simple",
            tagline VARCHAR(190) NULL,
            short_description TEXT NULL,
            description MEDIUMTEXT NULL,
            ingredients TEXT NULL,
            nutrition TEXT NULL,
            pack_label VARCHAR(60) NULL,
            variation_label VARCHAR(60) NULL,
            regular_price DECIMAL(10,2) NOT NULL DEFAULT 0,
            sale_price DECIMAL(10,2) NULL,
            price_min DECIMAL(10,2) NOT NULL DEFAULT 0,
            price_max DECIMAL(10,2) NOT NULL DEFAULT 0,
            stock_status ENUM("in_stock","out_of_stock") NOT NULL DEFAULT "in_stock",
            manage_stock TINYINT(1) NOT NULL DEFAULT 0,
            stock_qty INT NULL,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            is_bestseller TINYINT(1) NOT NULL DEFAULT 0,
            is_visible TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            meta_title VARCHAR(190) NULL,
            meta_description VARCHAR(320) NULL,
            source_id INT UNSIGNED NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL,
            UNIQUE KEY uq_products_slug (slug),
            KEY idx_products_category (category_id),
            KEY idx_products_listing (is_visible, deleted_at, sort_order),
            KEY idx_products_price (price_min),
            KEY idx_products_flags (is_featured, is_bestseller),
            FULLTEXT KEY ft_products (name, short_description),
            CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL
        )');

        $q('CREATE TABLE product_images (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id INT UNSIGNED NOT NULL,
            path VARCHAR(255) NOT NULL,
            alt_text VARCHAR(255) NULL,
            is_primary TINYINT(1) NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            KEY idx_pimg_product (product_id, sort_order),
            CONSTRAINT fk_pimg_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )');

        $q('CREATE TABLE product_variations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id INT UNSIGNED NOT NULL,
            label VARCHAR(80) NOT NULL,
            sku VARCHAR(60) NULL,
            regular_price DECIMAL(10,2) NOT NULL,
            sale_price DECIMAL(10,2) NULL,
            stock_status ENUM("in_stock","out_of_stock") NOT NULL DEFAULT "in_stock",
            stock_qty INT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            source_id INT UNSIGNED NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            KEY idx_pvar_product (product_id, sort_order),
            CONSTRAINT fk_pvar_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )');

        $q('CREATE TABLE product_attributes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id INT UNSIGNED NOT NULL,
            name VARCHAR(120) NOT NULL,
            value TEXT NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            KEY idx_pattr_product (product_id, sort_order),
            CONSTRAINT fk_pattr_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )');

        $q('CREATE TABLE stock_movements (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id INT UNSIGNED NOT NULL,
            variation_id INT UNSIGNED NULL,
            change_qty INT NOT NULL,
            reason VARCHAR(120) NOT NULL,
            reference VARCHAR(60) NULL,
            created_at DATETIME NULL,
            KEY idx_stock_product (product_id),
            CONSTRAINT fk_stock_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            CONSTRAINT fk_stock_variation FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE SET NULL
        )');

        $q('CREATE TABLE coupons (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(40) NOT NULL,
            description VARCHAR(190) NULL,
            type ENUM("percent","fixed") NOT NULL DEFAULT "percent",
            value DECIMAL(10,2) NOT NULL,
            min_subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
            max_discount DECIMAL(10,2) NULL,
            usage_limit INT NULL,
            used_count INT NOT NULL DEFAULT 0,
            starts_at DATETIME NULL,
            expires_at DATETIME NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_coupon_code (code)
        )');

        $q('CREATE TABLE carts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            token CHAR(40) NOT NULL,
            customer_id INT UNSIGNED NULL,
            coupon_code VARCHAR(40) NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_cart_token (token),
            KEY idx_cart_customer (customer_id),
            CONSTRAINT fk_cart_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        )');

        $q('CREATE TABLE cart_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            cart_id INT UNSIGNED NOT NULL,
            product_id INT UNSIGNED NOT NULL,
            variation_id INT UNSIGNED NULL,
            quantity INT UNSIGNED NOT NULL DEFAULT 1,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            KEY idx_ci_cart (cart_id),
            CONSTRAINT fk_ci_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
            CONSTRAINT fk_ci_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            CONSTRAINT fk_ci_variation FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE CASCADE
        )');

        $q('CREATE TABLE wishlists (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer_id INT UNSIGNED NOT NULL,
            product_id INT UNSIGNED NOT NULL,
            created_at DATETIME NULL,
            UNIQUE KEY uq_wishlist (customer_id, product_id),
            CONSTRAINT fk_wl_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
            CONSTRAINT fk_wl_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )');

        $q('CREATE TABLE orders (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(24) NOT NULL,
            access_token CHAR(40) NOT NULL,
            customer_id INT UNSIGNED NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            status ENUM("pending","processing","shipped","completed","cancelled","refunded") NOT NULL DEFAULT "pending",
            payment_status ENUM("unpaid","paid","failed","refunded") NOT NULL DEFAULT "unpaid",
            payment_method VARCHAR(40) NOT NULL DEFAULT "ccavenue",
            subtotal DECIMAL(10,2) NOT NULL,
            discount_total DECIMAL(10,2) NOT NULL DEFAULT 0,
            shipping_total DECIMAL(10,2) NOT NULL DEFAULT 0,
            grand_total DECIMAL(10,2) NOT NULL,
            coupon_code VARCHAR(40) NULL,
            shipping_method VARCHAR(60) NULL,
            billing_name VARCHAR(160) NOT NULL,
            billing_line1 VARCHAR(190) NOT NULL,
            billing_line2 VARCHAR(190) NULL,
            billing_city VARCHAR(90) NOT NULL,
            billing_state VARCHAR(90) NOT NULL,
            billing_postcode VARCHAR(12) NOT NULL,
            billing_country VARCHAR(60) NOT NULL DEFAULT "India",
            shipping_name VARCHAR(160) NOT NULL,
            shipping_phone VARCHAR(20) NOT NULL,
            shipping_line1 VARCHAR(190) NOT NULL,
            shipping_line2 VARCHAR(190) NULL,
            shipping_city VARCHAR(90) NOT NULL,
            shipping_state VARCHAR(90) NOT NULL,
            shipping_postcode VARCHAR(12) NOT NULL,
            shipping_country VARCHAR(60) NOT NULL DEFAULT "India",
            customer_note TEXT NULL,
            admin_note TEXT NULL,
            courier_name VARCHAR(80) NULL,
            tracking_number VARCHAR(80) NULL,
            tracking_url VARCHAR(255) NULL,
            paid_at DATETIME NULL,
            shipped_at DATETIME NULL,
            completed_at DATETIME NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_order_number (order_number),
            KEY idx_order_customer (customer_id),
            KEY idx_order_status (status, created_at),
            KEY idx_order_payment (payment_status),
            KEY idx_order_email (email),
            CONSTRAINT fk_order_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
        )');

        $q('CREATE TABLE order_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id INT UNSIGNED NOT NULL,
            product_id INT UNSIGNED NULL,
            variation_id INT UNSIGNED NULL,
            product_name VARCHAR(190) NOT NULL,
            variation_label VARCHAR(80) NULL,
            sku VARCHAR(60) NULL,
            image VARCHAR(255) NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            quantity INT UNSIGNED NOT NULL,
            line_total DECIMAL(10,2) NOT NULL,
            KEY idx_oi_order (order_id),
            CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
            CONSTRAINT fk_oi_variation FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE SET NULL
        )');

        $q('CREATE TABLE order_status_history (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id INT UNSIGNED NOT NULL,
            status VARCHAR(40) NOT NULL,
            note VARCHAR(255) NULL,
            changed_by VARCHAR(120) NULL,
            created_at DATETIME NULL,
            KEY idx_osh_order (order_id),
            CONSTRAINT fk_osh_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )');

        $q('CREATE TABLE payments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id INT UNSIGNED NOT NULL,
            gateway VARCHAR(40) NOT NULL,
            status VARCHAR(40) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            gateway_reference VARCHAR(120) NULL,
            bank_reference VARCHAR(120) NULL,
            payment_mode VARCHAR(80) NULL,
            message VARCHAR(255) NULL,
            raw_response TEXT NULL,
            verified TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NULL,
            KEY idx_pay_order (order_id),
            CONSTRAINT fk_pay_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )');
    }

    public function down(): void
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        foreach (['payments', 'order_status_history', 'order_items', 'orders', 'wishlists', 'cart_items', 'carts', 'coupons', 'stock_movements', 'product_attributes', 'product_variations', 'product_images', 'products', 'product_categories'] as $t) {
            $this->forge->dropTable($t, true);
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
