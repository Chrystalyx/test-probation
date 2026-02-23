<?php

namespace App\Http\Controllers\Web\Masters;

use App\Http\Controllers\Controller;

class InventoryController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Inventories'
        ];

        return view('masters.inventories.index', $data);
    }
}
