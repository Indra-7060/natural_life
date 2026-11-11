<?php

namespace App\Controllers;

use App\Libraries\Mailer;

/** Handles the contact enquiry form and the newsletter sign-up. */
class Contact extends BaseController
{
    public function submit()
    {
        // Honeypot: real visitors never fill the hidden "website" field.
        if ((string) $this->request->getPost('website') !== '') {
            return redirect()->to(site_url('contact-us'))->with('success', 'Thank you. Your message has been sent.');
        }
        if (service('throttler')->check('contact_' . md5($this->request->getIPAddress()), 5, 600) === false) {
            return redirect()->back()->withInput()->with('error', 'Too many messages in a short time. Please try again later.');
        }

        $rules = [
            'first_name' => ['label' => 'First name', 'rules' => 'required|min_length[2]|max_length[80]'],
            'last_name'  => ['label' => 'Last name', 'rules' => 'permit_empty|max_length[80]'],
            'email'      => ['label' => 'Email', 'rules' => 'required|valid_email|max_length[190]'],
            'phone'      => ['label' => 'Phone', 'rules' => 'permit_empty|regex_match[/^[0-9+\-\s]{10,15}$/]'],
            'subject'    => ['label' => 'Subject', 'rules' => 'permit_empty|max_length[190]'],
            'message'    => ['label' => 'Message', 'rules' => 'required|min_length[10]|max_length[3000]'],
        ];
        if (! $this->validate($rules)) {
            return redirect()->to(site_url('contact-us') . '#enquiry')->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = array_map(static fn ($v) => trim((string) $v), $this->request->getPost(['first_name', 'last_name', 'email', 'phone', 'subject', 'message']));
        $data['ip_address'] = $this->request->getIPAddress();
        $id                 = model('ContactEnquiryModel')->insert($data, true);
        (new Mailer())->adminNotice('New website enquiry from ' . $data['first_name'], 'admin_enquiry', ['enquiry' => $data + ['id' => $id]]);

        return redirect()->to(site_url('contact-us') . '#enquiry')->with('success', 'Thank you, ' . $data['first_name'] . '. Your message has been received and we will get back to you shortly.');
    }

    public function subscribe()
    {
        $ok      = $this->validate(['email' => 'required|valid_email|max_length[190]']) && (string) $this->request->getPost('website') === '';
        $message = 'Please enter a valid email address.';

        if ($ok) {
            $email = strtolower(trim((string) $this->request->getPost('email')));
            $model = model('NewsletterSubscriberModel');
            $row   = $model->where('email', $email)->first();
            if ($row) {
                $model->update($row['id'], ['is_active' => 1]);
                $message = 'You are already on our list. Thank you!';
            } else {
                $model->insert(['email' => $email, 'is_active' => 1, 'source' => 'website']);
                $message = 'Thank you for subscribing.';
            }
        }

        if ($this->isAjax()) {
            return $this->response->setStatusCode($ok ? 200 : 422)->setJSON(['ok' => $ok, 'message' => $message, 'csrf' => csrf_hash()]);
        }

        return redirect()->back()->with($ok ? 'success' : 'error', $message);
    }
}
