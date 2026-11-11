<?php

namespace App\Controllers\Admin;

use CodeIgniter\Exceptions\PageNotFoundException;

class Enquiries extends AdminController
{
    protected string $module = 'inbox';

    public const STATUSES = ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'closed' => 'Closed'];

    public function index(): string
    {
        $model  = model('ContactEnquiryModel');
        $q      = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('status');
        if ($q !== '') {
            $model->groupStart()->like('first_name', $q)->orLike('last_name', $q)->orLike('email', $q)->orLike('subject', $q)->orLike('message', $q)->groupEnd();
        }
        if (isset(self::STATUSES[$status])) {
            $model->where('status', $status);
        }

        return $this->render('admin/inbox/enquiries', [
            'title' => 'Enquiries', 'breadcrumbs' => [['Inbox', null], ['Enquiries', null]],
            'rows' => $model->orderBy('id', 'DESC')->paginate(20, 'admin'), 'pager' => $model->pager, 'q' => $q, 'status' => $status, 'statuses' => self::STATUSES,
        ]);
    }

    public function show(int $id): string
    {
        $model   = model('ContactEnquiryModel');
        $enquiry = $model->find($id);
        if (! $enquiry) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ($enquiry['status'] === 'new') {
            $model->update($id, ['status' => 'read']);
            $enquiry['status'] = 'read';
        }

        return $this->render('admin/inbox/enquiry', [
            'title' => 'Enquiry from ' . $enquiry['first_name'], 'breadcrumbs' => [['Inbox', null], ['Enquiries', site_url('admin/enquiries')], ['#' . $id, null]],
            'enquiry' => $enquiry, 'statuses' => self::STATUSES,
        ]);
    }

    public function update(int $id)
    {
        $model = model('ContactEnquiryModel');
        if (! $model->find($id)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $status = (string) $this->request->getPost('status');
        $model->update($id, [
            'status'     => isset(self::STATUSES[$status]) ? $status : 'read',
            'admin_note' => mb_substr(trim((string) $this->request->getPost('admin_note')), 0, 2000) ?: null,
        ]);

        return redirect()->back()->with('success', 'Enquiry updated.');
    }

    public function delete(int $id)
    {
        model('ContactEnquiryModel')->delete($id);

        return $this->back('admin/enquiries', 'Enquiry deleted.');
    }
}
