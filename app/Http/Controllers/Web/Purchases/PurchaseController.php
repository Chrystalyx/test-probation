<?php

namespace App\Http\Controllers\Web\Purchases;

use App\Http\Controllers\Controller;

class PurchaseController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Purchases'
        ];

        return view('purchases.index', $data);
    }

    public function form($id = null)
    {
        $data = [
            'title' => $id ? 'Edit Purchase' : 'Add Purchase',
            'id' => $id
        ];

        return view('purchases.form', $data);
    }
}
