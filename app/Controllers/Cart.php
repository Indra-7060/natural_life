<?php

namespace App\Controllers;

use RuntimeException;

class Cart extends BaseController
{
    public function index(): string
    {
        return $this->render('cart/index', [
            'lines'  => $this->cart->lines(),
            'totals' => $this->cart->totals(),
            'breadcrumbs' => [['Home', site_url('/')], ['Cart', null]],
        ], $this->seo(['title' => 'Your cart', 'noindex' => true]));
    }

    /** Mini-cart drawer contents (JSON). */
    public function drawer()
    {
        return $this->response->setJSON($this->cartPayload());
    }

    public function add()
    {
        $productId   = (int) $this->request->getPost('product_id');
        $variationId = (int) $this->request->getPost('variation_id') ?: null;
        $qty         = (int) ($this->request->getPost('quantity') ?: 1);

        try {
            $this->cart->add($productId, $variationId, $qty);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->done('Added to your cart.');
    }

    public function update()
    {
        $this->cart->update((int) $this->request->getPost('item_id'), (int) $this->request->getPost('quantity'));

        return $this->done('Cart updated.');
    }

    public function remove()
    {
        $this->cart->remove((int) $this->request->getPost('item_id'));

        return $this->done('Item removed from your cart.');
    }

    public function applyCoupon()
    {
        try {
            $this->cart->applyCoupon((string) $this->request->getPost('code'));
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->done('Coupon applied.');
    }

    public function removeCoupon()
    {
        $this->cart->removeCoupon();

        return $this->done('Coupon removed.');
    }

    private function done(string $message)
    {
        if ($this->isAjax()) {
            return $this->response->setJSON($this->cartPayload(['message' => $message]));
        }

        return redirect()->back()->with('success', $message);
    }

    private function fail(string $message)
    {
        if ($this->isAjax()) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => $message, 'csrf' => csrf_hash()]);
        }

        return redirect()->back()->with('error', $message);
    }
}
