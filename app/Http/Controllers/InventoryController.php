<?php

namespace App\Http\Controllers;

use App\Models\Inventories;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== Role::SUPER_ADMIN) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $inventories = Inventories::latest()->get();
        return view('inventory.index', compact('inventories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:inventories,code,' . $request->id,
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        Inventories::updateOrCreate(
            ['id' => $request->id],
            [
                'code' => $request->code,
                'name' => $request->name,
                'price' => $request->price,
                'stock' => $request->stock,
            ]
        );

        return response()->json(['success' => 'Data barang berhasil disimpan!']);
    }

    public function edit($id)
    {
        $inventory = Inventories::find($id);
        return response()->json($inventory);
    }

    public function destroy($id)
    {
        Inventories::find($id)->delete();
        return response()->json(['success' => 'Data barang berhasil dihapus!']);
    }
}
