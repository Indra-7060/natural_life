<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Site settings. Every value below was read from natural-life.in on 2026-11-11;
 * values the source site does not publish are left empty for the client to fill in.
 * Existing values are never overwritten on re-seed.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // [group, key, value, label, type, help]
        $rows = [
            ['general', 'site_name', 'Sugarlif', 'Brand name', 'text', 'Shown in the browser title and e-mails.'],
            ['general', 'company_name', 'Natural Life Speciality Pvt. Ltd.', 'Company name', 'text', null],
            ['general', 'site_tagline', 'Natural Low GI Foods', 'Tagline', 'text', null],
            ['general', 'logo', 'brand/sugarlife-logo-1.svg', 'Logo (light backgrounds)', 'image', null],
            ['general', 'logo_light', 'brand/sugarlife-logo-white-1.svg', 'Logo (dark backgrounds)', 'image', null],
            ['general', 'favicon', 'brand/cropped-sugarlife-logo-1.jpg', 'Favicon', 'image', 'Square image, at least 180 x 180 px.'],
            ['general', 'announcement_text', 'Free shipping on orders of ₹500 or more', 'Announcement bar text', 'text', 'Leave empty to hide the bar.'],
            ['general', 'announcement_link_label', 'Shop now', 'Announcement link label', 'text', null],
            ['general', 'announcement_link_url', '/shop', 'Announcement link URL', 'text', null],
            ['general', 'default_meta_description', 'Sugarlif Low GI foods by Natural Life Speciality Pvt. Ltd. — natural cane sugar, whole wheat atta, honey and jaggery re-engineered to be Low GI.', 'Default meta description', 'textarea', 'Used when a page has no description of its own.'],

            ['contact', 'contact_email_primary', 'info@natural-life.in', 'Primary e-mail', 'text', null],
            ['contact', 'contact_email_sales', 'sales@natural-life.in', 'Sales e-mail', 'text', null],
            ['contact', 'contact_phone', '+91 800 729 1595', 'Phone', 'text', null],
            ['contact', 'contact_whatsapp', '918007291595', 'WhatsApp number', 'text', 'Digits only, with country code.'],
            ['contact', 'bulk_order_note', 'If you need to order in bulk, please don’t hesitate to contact us on +918007291595 via call or WhatsApp.', 'Bulk order note', 'textarea', null],
            ['contact', 'address_line1', 'F-7, Sacred World Mall, Wanwadi', 'Address', 'text', null],
            ['contact', 'address_city', 'Pune', 'City', 'text', null],
            ['contact', 'address_state', 'Maharashtra', 'State', 'text', null],
            ['contact', 'address_postcode', '411040', 'PIN code', 'text', null],
            ['contact', 'notification_email', 'info@natural-life.in', 'Send order and enquiry notifications to', 'text', null],

            ['shop', 'shipping_flat_rate', '40', 'Standard shipping charge (₹)', 'number', 'Charged when the order subtotal is below the free-shipping threshold.'],
            ['shop', 'free_shipping_threshold', '500', 'Free shipping from subtotal (₹)', 'number', 'Set to 0 to always charge shipping.'],
            ['shop', 'order_processing_time', '1 business day', 'Order processing time', 'text', 'From the Shipping Policy; shown on product pages.'],
            ['shop', 'shipping_region_note', 'We deliver across India.', 'Delivery region note', 'text', null],
            ['shop', 'gst_number', '', 'GSTIN', 'text', 'Not published on the original site.'],
            ['shop', 'fssai_number', '', 'FSSAI licence number', 'text', 'Not published on the original site.'],

            ['footer', 'footer_about', 'Natural Life Speciality Pvt. Ltd. is a premier food company pioneering the Low GI food revolution. The company manufactures and sells Low GI food products in the brand name ‘Sugarlif’.', 'Footer company description', 'textarea', null],
            ['footer', 'innovator_label', 'Visit CKN Innovations website', 'Innovator link label', 'text', null],
            ['footer', 'innovator_url', 'https://ckninnovations.com/', 'Innovator link URL', 'text', null],
            ['footer', 'newsletter_heading', 'Sign up for our newsletter', 'Newsletter heading', 'text', null],
            ['footer', 'newsletter_text', 'Be the first to know about our special offers, news, and updates.', 'Newsletter text', 'text', null],
            ['footer', 'copyright_name', 'Natural Life Speciality', 'Copyright owner', 'text', 'The year is added automatically.'],

            ['marketplaces', 'marketplace_amazon', 'https://www.amazon.in/dp/B07BQP6C4Z/ref=cm_sw_r_apan_glt_i_35H5YC8JNTSHMXSJ0PKD', 'Amazon URL', 'text', null],
            ['marketplaces', 'marketplace_flipkart', 'https://dl.flipkart.com/s/PBFSAyuuuN', 'Flipkart URL', 'text', null],
            ['marketplaces', 'marketplace_jiomart', 'https://www.jiomart.com/catalogsearch/result?q=sugarlif', 'JioMart URL', 'text', null],
            ['marketplaces', 'marketplace_1mg', 'https://www.1mg.com/search/all?name=sugarlif', 'Tata 1mg URL', 'text', null],

            ['social', 'social_facebook', '', 'Facebook URL', 'text', 'Not published on the original site.'],
            ['social', 'social_instagram', '', 'Instagram URL', 'text', 'Not published on the original site.'],
            ['social', 'social_youtube', '', 'YouTube URL', 'text', 'Not published on the original site.'],
            ['social', 'social_linkedin', '', 'LinkedIn URL', 'text', 'Not published on the original site.'],
        ];

        $table = $this->db->table('settings');
        foreach ($rows as $i => [$group, $key, $value, $label, $type, $help]) {
            $meta = ['group' => $group, 'label' => $label, 'type' => $type, 'help' => $help, 'sort_order' => $i, 'updated_at' => date('Y-m-d H:i:s')];
            if ($table->where('key', $key)->countAllResults()) {
                $this->db->table('settings')->where('key', $key)->update($meta);
            } else {
                $this->db->table('settings')->insert($meta + ['key' => $key, 'value' => $value]);
            }
        }
    }
}
