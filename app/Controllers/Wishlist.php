<?php

namespace App\Controllers;

class Wishlist extends BaseController
{
    public function index(): string
    {
        return $this->render('account/wishlist', [
            'products'    => $this->wishlist->products(),
            'breadcrumbs' => [['Home', site_url('/')], ['Wishlist', null]],
        ], $this->seo(['title' => 'Your wishlist', 'noindex' => true]));
    }

    public function toggle()
    {
        $productId = (int) $this->request->getPost('product_id');
        if (! model('ProductModel')->where(['id' => $productId, 'is_visible' => 1])->first()) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'message' => 'Product not found.', 'csrf' => csrf_hash()]);
        }
        $added = $this->wishlist->toggle($productId);
        $msg   = $added ? 'Saved to your wishlist.' : 'Removed from your wishlist.';

        if ($this->isAjax()) {
            return $this->response->setJSON(['ok' => true, 'added' => $added, 'count' => count($this->wishlist->ids()), 'message' => $msg, 'csrf' => csrf_hash()]);
        }

        return redirect()->back()->with('success', $msg);
    }
}
