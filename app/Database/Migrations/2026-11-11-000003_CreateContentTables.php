<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** CMS content: pages, sections, banners, blog, FAQs, testimonials, team, certifications, enquiries, newsletter. */
class CreateContentTables extends Migration
{
    private string $opts = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function up(): void
    {
        $q = fn (string $sql) => $this->db->query($sql . ' ' . $this->opts);

        $q('CREATE TABLE pages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(160) NOT NULL,
            title VARCHAR(190) NOT NULL,
            template VARCHAR(40) NOT NULL DEFAULT "default",
            eyebrow VARCHAR(120) NULL,
            intro TEXT NULL,
            body MEDIUMTEXT NULL,
            hero_image VARCHAR(255) NULL,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            is_system TINYINT(1) NOT NULL DEFAULT 0,
            meta_title VARCHAR(190) NULL,
            meta_description VARCHAR(320) NULL,
            og_image VARCHAR(255) NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_pages_slug (slug)
        )');

        $q('CREATE TABLE page_sections (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page_id INT UNSIGNED NOT NULL,
            section_key VARCHAR(60) NOT NULL,
            admin_label VARCHAR(120) NOT NULL,
            eyebrow VARCHAR(120) NULL,
            heading VARCHAR(255) NULL,
            body MEDIUMTEXT NULL,
            image VARCHAR(255) NULL,
            image_alt VARCHAR(255) NULL,
            cta_label VARCHAR(80) NULL,
            cta_url VARCHAR(255) NULL,
            items MEDIUMTEXT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            updated_at DATETIME NULL,
            UNIQUE KEY uq_section (page_id, section_key),
            KEY idx_section_order (page_id, sort_order),
            CONSTRAINT fk_section_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
        )');

        $q('CREATE TABLE banners (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            eyebrow VARCHAR(120) NULL,
            heading VARCHAR(255) NOT NULL,
            subheading TEXT NULL,
            image VARCHAR(255) NULL,
            image_alt VARCHAR(255) NULL,
            cta_label VARCHAR(80) NULL,
            cta_url VARCHAR(255) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NULL, updated_at DATETIME NULL
        )');

        $q('CREATE TABLE blog_categories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(140) NOT NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_bcat_slug (slug)
        )');

        $q('CREATE TABLE blog_posts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id INT UNSIGNED NULL,
            title VARCHAR(220) NOT NULL,
            slug VARCHAR(240) NOT NULL,
            excerpt TEXT NULL,
            content MEDIUMTEXT NULL,
            image VARCHAR(255) NULL,
            author_name VARCHAR(120) NULL,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            published_at DATETIME NULL,
            meta_title VARCHAR(190) NULL,
            meta_description VARCHAR(320) NULL,
            source_id INT UNSIGNED NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_post_slug (slug),
            KEY idx_post_listing (is_published, published_at),
            CONSTRAINT fk_post_category FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL
        )');

        $q('CREATE TABLE faqs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            question VARCHAR(255) NOT NULL,
            answer MEDIUMTEXT NOT NULL,
            show_on_home TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            KEY idx_faq_order (is_active, sort_order)
        )');

        $q('CREATE TABLE testimonials (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            meta VARCHAR(160) NULL,
            title VARCHAR(190) NULL,
            content TEXT NOT NULL,
            rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
            image VARCHAR(255) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            KEY idx_testimonial_order (is_active, sort_order)
        )');

        $q('CREATE TABLE team_members (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            designation VARCHAR(160) NULL,
            `group` VARCHAR(60) NOT NULL DEFAULT "Key People",
            bio TEXT NULL,
            image VARCHAR(255) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            KEY idx_team_order (is_active, sort_order)
        )');

        $q('CREATE TABLE certifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(190) NOT NULL,
            issuer_location VARCHAR(120) NULL,
            description TEXT NULL,
            image VARCHAR(255) NULL,
            document VARCHAR(255) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            KEY idx_cert_order (is_active, sort_order)
        )');

        $q('CREATE TABLE contact_enquiries (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(80) NOT NULL,
            last_name VARCHAR(80) NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(20) NULL,
            subject VARCHAR(190) NULL,
            message TEXT NOT NULL,
            status ENUM("new","read","replied","closed") NOT NULL DEFAULT "new",
            admin_note TEXT NULL,
            ip_address VARCHAR(45) NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            KEY idx_enquiry_status (status, created_at)
        )');

        $q('CREATE TABLE newsletter_subscribers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(190) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            source VARCHAR(40) NULL,
            created_at DATETIME NULL, updated_at DATETIME NULL,
            UNIQUE KEY uq_subscriber_email (email)
        )');
    }

    public function down(): void
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        foreach (['newsletter_subscribers', 'contact_enquiries', 'certifications', 'team_members', 'testimonials', 'faqs', 'blog_posts', 'blog_categories', 'banners', 'page_sections', 'pages'] as $t) {
            $this->forge->dropTable($t, true);
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
