<?php

namespace App\Controllers;

use App\Libraries\Mailer;
use App\Models\OrderModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Account extends BaseController
{
    // ------------------------------------------------------------------ authentication

    public function login()
    {
        if ($this->customer) {
            return redirect()->to(site_url('my-account'));
        }

        return $this->render('account/login', ['breadcrumbs' => [['Home', site_url('/')], ['My account', null]]],
            $this->seo(['title' => 'Sign in or create an account', 'noindex' => true]));
    }

    public function attemptLogin()
    {
        if (! $this->validate(['email' => 'required|valid_email', 'password' => 'required'])) {
            return redirect()->back()->withInput()->with('error', 'Please enter your email and password.');
        }
        // Basic brute-force throttle: 8 attempts per 10 minutes per IP.
        if (service('throttler')->check('login_' . md5($this->request->getIPAddress()), 8, 600) === false) {
            return redirect()->back()->with('error', 'Too many sign-in attempts. Please wait a few minutes and try again.');
        }

        $customers = model('CustomerModel');
        $customer  = $customers->where('email', strtolower(trim((string) $this->request->getPost('email'))))->first();
        if (! $customer || ! $customer['is_active'] || ! password_verify((string) $this->request->getPost('password'), $customer['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'The email or password is incorrect.');
        }

        return $this->signIn($customer, 'Welcome back, ' . $customer['first_name'] . '.');
    }

    public function attemptRegister()
    {
        $rules = [
            'first_name'       => ['label' => 'First name', 'rules' => 'required|min_length[2]|max_length[80]'],
            'last_name'        => ['label' => 'Last name', 'rules' => 'required|max_length[80]'],
            'reg_email'        => ['label' => 'Email', 'rules' => 'required|valid_email|max_length[190]|is_unique[customers.email]', 'errors' => ['is_unique' => 'An account with this email already exists. Please sign in instead.']],
            'reg_password'     => ['label' => 'Password', 'rules' => 'required|min_length[8]|max_length[72]'],
            'password_confirm' => ['label' => 'Confirm password', 'rules' => 'required|matches[reg_password]'],
            'accept_terms'     => ['label' => 'Terms', 'rules' => 'required', 'errors' => ['required' => 'Please accept the Terms and Privacy Policy.']],
        ];
        if (! $this->validate($rules)) {
            return redirect()->to(site_url('my-account/login') . '#register')->withInput()->with('errors', $this->validator->getErrors())->with('form', 'register');
        }

        $customers = model('CustomerModel');
        $id        = $customers->insert([
            'first_name'    => trim((string) $this->request->getPost('first_name')),
            'last_name'     => trim((string) $this->request->getPost('last_name')),
            'email'         => strtolower(trim((string) $this->request->getPost('reg_email'))),
            'password_hash' => password_hash((string) $this->request->getPost('reg_password'), PASSWORD_DEFAULT),
            'is_active'     => 1,
        ], true);
        $customer = $customers->find($id);
        (new Mailer())->send($customer['email'], 'Welcome to Sugarlif', 'welcome', ['customer' => $customer]);

        return $this->signIn($customer, 'Your account has been created. Welcome to Sugarlif!');
    }

    private function signIn(array $customer, string $message)
    {
        $intended = session('customer_intended');
        session()->regenerate(true);
        session()->set('customer_id', $customer['id']);
        session()->remove('customer_intended');
        model('CustomerModel')->update($customer['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
        $this->cart->attachCustomer((int) $customer['id']);
        $this->wishlist->attachCustomer((int) $customer['id']);

        return redirect()->to($intended && str_starts_with($intended, site_url()) ? $intended : site_url('my-account'))->with('success', $message);
    }

    public function logout()
    {
        session()->remove(['customer_id', 'cart_token', 'wishlist']);
        session()->regenerate(true);

        return redirect()->to(site_url('/'))->with('success', 'You have been signed out.');
    }

    public function forgot(): string
    {
        return $this->render('account/forgot', [], $this->seo(['title' => 'Reset your password', 'noindex' => true]));
    }

    public function sendReset()
    {
        if (! $this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid email address.');
        }
        if (service('throttler')->check('reset_' . md5($this->request->getIPAddress()), 5, 900) === false) {
            return redirect()->back()->with('error', 'Too many requests. Please try again later.');
        }
        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $customer = model('CustomerModel')->where(['email' => $email, 'is_active' => 1])->first();
        if ($customer) {
            $token = bin2hex(random_bytes(32));
            model('PasswordResetModel')->insert([
                'user_type' => 'customer', 'email' => $email, 'token_hash' => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            ]);
            (new Mailer())->send($email, 'Reset your Sugarlif password', 'password_reset', [
                'customer' => $customer, 'link' => site_url('my-account/reset-password/' . $token),
            ]);
        }

        // Same message whether or not the account exists, so addresses cannot be probed.
        return redirect()->to(site_url('my-account/login'))->with('success', 'If an account exists for that email, a reset link has been sent.');
    }

    public function reset(string $token)
    {
        if (! $this->validResetToken($token)) {
            return redirect()->to(site_url('my-account/forgot-password'))->with('error', 'This reset link is invalid or has expired. Please request a new one.');
        }

        return $this->render('account/reset', ['token' => $token], $this->seo(['title' => 'Choose a new password', 'noindex' => true]));
    }

    public function doReset(string $token)
    {
        $row = $this->validResetToken($token);
        if (! $row) {
            return redirect()->to(site_url('my-account/forgot-password'))->with('error', 'This reset link is invalid or has expired.');
        }
        if (! $this->validate(['password' => 'required|min_length[8]|max_length[72]', 'password_confirm' => 'required|matches[password]'])) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }
        model('CustomerModel')->where('email', $row['email'])->set(['password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT)])->update();
        model('PasswordResetModel')->update($row['id'], ['used_at' => date('Y-m-d H:i:s')]);

        return redirect()->to(site_url('my-account/login'))->with('success', 'Your password has been changed. Please sign in.');
    }

    private function validResetToken(string $token): ?array
    {
        if (! ctype_xdigit($token) || strlen($token) !== 64) {
            return null;
        }

        return model('PasswordResetModel')->where(['token_hash' => hash('sha256', $token), 'user_type' => 'customer', 'used_at' => null])
            ->where('expires_at >', date('Y-m-d H:i:s'))->first();
    }

    // ------------------------------------------------------------------ account area (customerauth filter)

    public function dashboard(): string
    {
        $orders = model(OrderModel::class)->where('customer_id', $this->customer['id'])->orderBy('id', 'DESC');

        return $this->account('dashboard', 'My account', [
            'orders'     => $orders->findAll(3),
            'orderCount' => model(OrderModel::class)->where('customer_id', $this->customer['id'])->countAllResults(),
            'address'    => model('CustomerAddressModel')->where('customer_id', $this->customer['id'])->orderBy('is_default', 'DESC')->first(),
        ]);
    }

    public function orders(): string
    {
        $model = model(OrderModel::class);

        return $this->account('orders', 'My orders', [
            'orders' => $model->where('customer_id', $this->customer['id'])->orderBy('id', 'DESC')->paginate(10, 'orders'),
            'pager'  => $model->pager,
        ]);
    }

    public function order(string $number): string
    {
        $model = model(OrderModel::class);
        $order = $model->where(['order_number' => $number, 'customer_id' => $this->customer['id']])->first();
        if (! $order) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->account('order', 'Order ' . $order['order_number'], ['order' => $model->withDetails($order)]);
    }

    public function addresses(): string
    {
        return $this->account('addresses', 'My addresses', [
            'addresses' => model('CustomerAddressModel')->where('customer_id', $this->customer['id'])->orderBy('is_default', 'DESC')->orderBy('id', 'DESC')->findAll(),
            'editing'   => ($id = (int) $this->request->getGet('edit')) ? model('CustomerAddressModel')->where(['id' => $id, 'customer_id' => $this->customer['id']])->first() : null,
        ]);
    }

    public function saveAddress()
    {
        $rules = [
            'label'     => 'required|max_length[40]',
            'full_name' => 'required|min_length[2]|max_length[160]',
            'phone'     => 'required|regex_match[/^[0-9+\-\s]{10,15}$/]',
            'line1'     => 'required|min_length[5]|max_length[190]',
            'line2'     => 'permit_empty|max_length[190]',
            'city'      => 'required|max_length[90]',
            'state'     => 'required|in_list[' . implode(',', indian_states()) . ']',
            'postcode'  => 'required|regex_match[/^[1-9][0-9]{5}$/]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $model = model('CustomerAddressModel');
        $data  = array_map('trim', $this->request->getPost(['label', 'full_name', 'phone', 'line1', 'line2', 'city', 'state', 'postcode']));
        $data['customer_id'] = $this->customer['id'];
        $data['is_default']  = $this->request->getPost('is_default') ? 1 : 0;
        if ($data['is_default'] || $model->where('customer_id', $this->customer['id'])->countAllResults() === 0) {
            $model->where('customer_id', $this->customer['id'])->set('is_default', 0)->update();
            $data['is_default'] = 1;
        }
        $id = (int) $this->request->getPost('id');
        if ($id && $model->where(['id' => $id, 'customer_id' => $this->customer['id']])->first()) {
            $model->update($id, $data);
        } else {
            $model->insert($data);
        }

        return redirect()->to(site_url('my-account/addresses'))->with('success', 'Address saved.');
    }

    public function deleteAddress(int $id)
    {
        model('CustomerAddressModel')->where(['id' => $id, 'customer_id' => $this->customer['id']])->delete();

        return redirect()->to(site_url('my-account/addresses'))->with('success', 'Address removed.');
    }

    public function profile(): string
    {
        return $this->account('profile', 'Account details', []);
    }

    public function updateProfile()
    {
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[80]',
            'last_name'  => 'required|max_length[80]',
            'phone'      => 'permit_empty|regex_match[/^[0-9+\-\s]{10,15}$/]',
            'email'      => 'required|valid_email|is_unique[customers.email,id,' . $this->customer['id'] . ']',
        ];
        $changing = (string) $this->request->getPost('new_password') !== '';
        if ($changing) {
            $rules += ['current_password' => 'required', 'new_password' => 'min_length[8]|max_length[72]', 'new_password_confirm' => 'matches[new_password]'];
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data = [
            'first_name' => trim((string) $this->request->getPost('first_name')), 'last_name' => trim((string) $this->request->getPost('last_name')),
            'phone' => trim((string) $this->request->getPost('phone')), 'email' => strtolower(trim((string) $this->request->getPost('email'))),
        ];
        if ($changing) {
            if (! password_verify((string) $this->request->getPost('current_password'), $this->customer['password_hash'])) {
                return redirect()->back()->withInput()->with('error', 'Your current password is incorrect.');
            }
            $data['password_hash'] = password_hash((string) $this->request->getPost('new_password'), PASSWORD_DEFAULT);
        }
        model('CustomerModel')->update($this->customer['id'], $data);

        return redirect()->to(site_url('my-account/profile'))->with('success', 'Your details have been updated.');
    }

    private function account(string $view, string $title, array $data): string
    {
        $data['accountView']  = $view;
        $data['accountTitle'] = $title;
        $data['breadcrumbs']  = [['Home', site_url('/')], ['My account', $view === 'dashboard' ? null : site_url('my-account')]];
        if ($view !== 'dashboard') {
            $data['breadcrumbs'][] = [$title, null];
        }

        return $this->render('account/' . $view, $data, $this->seo(['title' => $title, 'noindex' => true]));
    }
}
