<?php

namespace App\Controllers\Admin;

class Subscribers extends AdminController
{
    protected string $module = 'inbox';

    public function index(): string
    {
        $model = model('NewsletterSubscriberModel');
        $q     = trim((string) $this->request->getGet('q'));
        if ($q !== '') {
            $model->like('email', $q);
        }

        return $this->render('admin/inbox/subscribers', [
            'title' => 'Newsletter subscribers', 'breadcrumbs' => [['Inbox', null], ['Newsletter', null]],
            'rows' => $model->orderBy('id', 'DESC')->paginate(30, 'admin'), 'pager' => $model->pager, 'q' => $q,
            'activeCount' => model('NewsletterSubscriberModel')->where('is_active', 1)->countAllResults(),
        ]);
    }

    /** CSV download of active subscribers. */
    public function export()
    {
        $rows = model('NewsletterSubscriberModel')->where('is_active', 1)->orderBy('id')->findAll();
        $csv  = "email,subscribed_at,source\r\n";
        foreach ($rows as $r) {
            // Guard against spreadsheet formula injection.
            $email = preg_match('/^[=+\-@]/', $r['email']) ? "'" . $r['email'] : $r['email'];
            $csv .= '"' . str_replace('"', '""', $email) . '",' . $r['created_at'] . ',' . ($r['source'] ?? '') . "\r\n";
        }

        return $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="newsletter-subscribers-' . date('Y-m-d') . '.csv"')->setBody($csv);
    }

    public function toggle(int $id)
    {
        $model = model('NewsletterSubscriberModel');
        if ($row = $model->find($id)) {
            $model->update($id, ['is_active' => $row['is_active'] ? 0 : 1]);
        }

        return redirect()->back()->with('success', 'Subscriber updated.');
    }

    public function delete(int $id)
    {
        model('NewsletterSubscriberModel')->delete($id);

        return redirect()->back()->with('success', 'Subscriber removed.');
    }
}
