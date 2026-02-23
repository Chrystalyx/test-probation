<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;

class SaleController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Sales'
        ];

        return view('sales.index', $data);
    }

    public function form($id = null)
    {
        $data = [
            'title' => $id ? 'Edit Sale' : 'Add Sale',
            'id' => $id
        ];

        return view('sales.form', $data);
    }
}
