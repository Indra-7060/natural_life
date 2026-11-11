<?php

namespace App\Controllers\Admin;

use App\Libraries\CcavenueGateway;
use App\Libraries\Mailer;
use App\Libraries\MediaLibrary;
use CodeIgniter\Exceptions\PageNotFoundException;

class Settings extends AdminController
{
    protected string $module = 'settings';

    public const GROUPS = [
        'general'      => ['General', 'Brand, logo and announcement bar'],
        'contact'      => ['Contact details', 'Shown in the header, footer and contact page'],
        'shop'         => ['Shop & shipping', 'Shipping charges and store details'],
        'footer'       => ['Footer', 'Footer text and newsletter block'],
        'marketplaces' => ['Marketplaces', '“Also available on” links'],
        'social'       => ['Social media', 'Profile links (hidden when empty)'],
    ];

    public function index(string $group = 'general'): string
    {
        if (! isset(self::GROUPS[$group])) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/settings/index', [
            'title'       => 'Settings',
            'breadcrumbs' => [['Configuration', null], ['Settings', site_url('admin/settings')], [self::GROUPS[$group][0], null]],
            'group'       => $group,
            'groups'      => self::GROUPS,
            'rows'        => model('SettingModel')->where('group', $group)->orderBy('sort_order')->findAll(),
            // Read-only status of the secrets kept in .env (values are never shown).
            'gateway'     => ['ready' => ($g = new CcavenueGateway())->isConfigured(), 'mode' => $g->mode()],
            'smtpReady'   => (new Mailer())->isConfigured(),
        ]);
    }

    public function save(string $group)
    {
        if (! isset(self::GROUPS[$group])) {
            throw PageNotFoundException::forPageNotFound();
        }
        $model   = model('SettingModel');
        $library = new MediaLibrary();

        foreach ($model->where('group', $group)->findAll() as $row) {
            $key   = $row['key'];
            $value = trim((string) $this->request->getPost($key));

            if ($row['type'] === 'number') {
                if ($value !== '' && (! is_numeric($value) || (float) $value < 0)) {
                    return redirect()->back()->withInput()->with('error', $row['label'] . ' must be a number of 0 or more.');
                }
            } elseif ($row['type'] === 'image') {
                $file = $this->request->getFile($key . '_upload');
                if ($file && $file->isValid()) {
                    try {
                        $value = $library->store($file, 'brand')['path'];
                    } catch (\RuntimeException $e) {
                        return redirect()->back()->with('error', $e->getMessage());
                    }
                }
            } elseif (str_ends_with($key, '_url') || str_starts_with($key, 'social_') || str_starts_with($key, 'marketplace_')) {
                if ($value !== '' && ! preg_match('#^(https?://|/)#i', $value)) {
                    return redirect()->back()->withInput()->with('error', $row['label'] . ' must start with https:// (or / for a page on this site).');
                }
            } elseif (str_contains($key, 'email')) {
                if ($value !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return redirect()->back()->withInput()->with('error', $row['label'] . ' must be a valid email address.');
                }
            }
            $model->update($row['id'], ['value' => mb_substr($value, 0, 5000)]);
        }
        $this->flushCache();

        return redirect()->to(site_url('admin/settings/' . $group))->with('success', 'Settings saved.');
    }
}
