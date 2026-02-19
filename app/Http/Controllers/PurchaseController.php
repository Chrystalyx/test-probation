<?php

namespace App\Http\Controllers;

use App\Models\Purchases;
use App\Models\PurchaseDetails;
use App\Models\Inventories;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === Role::SALES) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $query = Purchases::with(['user', 'details.inventory'])->latest();

        if ($user->role === Role::PURCHASE) {
            $query->where('user_id', $user->id);
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('total_price', function ($row) {
                    return 'Rp ' . number_format($row->details->sum(fn($d) => $d->price * $d->qty), 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    return '<a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-info btn-sm showDetail">Detail</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('purchases.index');
    }

    public function create()
    {
        $user = Auth::user();

        if (!in_array($user->role, [Role::SUPER_ADMIN, Role::PURCHASE])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $inventories = Inventories::all();

        $today = date('Ymd');
        $lastPurchase = Purchases::whereDate('created_at', now())->latest()->first();

        if ($lastPurchase) {
            $lastNumber = (int) substr($lastPurchase->number, -4);
            $nextSequence = $lastNumber + 1;
        } else {
            $nextSequence = 1;
        }

        $number = 'PO-' . $today . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

        return view('purchases.create', compact('inventories', 'number'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|unique:purchases,number',
            'date'   => 'required|date',
            'items'  => 'required|array',
            'items.*.inventory_id' => 'required|exists:inventories,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $purchase = Purchases::create([
                'number'  => $request->number,
                'date'    => $request->date,
                'user_id' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $inventory = Inventories::lockForUpdate()->find($item['inventory_id']);

                $inventory->increment('stock', $item['qty']);

                PurchaseDetails::create([
                    'purchase_id'  => $purchase->id,
                    'inventory_id' => $item['inventory_id'],
                    'qty'          => $item['qty'],
                    'price'        => $item['price'],
                ]);
            }

            DB::commit();
            return response()->json(['success' => 'Transaksi Pembelian Berhasil Disimpan!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $purchase = Purchases::with(['details.inventory', 'user'])->findOrFail($id);
        return response()->json($purchase);
    }
}
