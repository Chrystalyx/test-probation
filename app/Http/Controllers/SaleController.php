<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\SalesDetails;
use App\Models\Inventories;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Sales::with(['user', 'details.inventory'])->latest();

        if ($user->role === Role::SALES) {
            $query->where('user_id', $user->id);
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('total_price', function ($row) {
                    return 'Rp ' . number_format($row->details->sum(fn($d) => $d->price * $d->qty), 0, ',', '.');
                })
                ->addColumn('action', function ($row) use ($user) {
                    $btn = '<a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-info btn-sm showDetail me-1"><i class="bi bi-eye"></i> Detail</a>';

                    if ($user->role !== Role::MANAGER) {
                        $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-danger btn-sm deleteSale"><i class="bi bi-trash"></i> Hapus</a>';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('sales.index');
    }

    public function create()
    {
        if (Auth::user()->role === Role::MANAGER) {
            abort(403);
        }

        $inventories = Inventories::where('stock', '>', 0)->get();
        $today = date('Ymd');
        $lastSale = Sales::whereDate('created_at', today())->latest()->first();

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->number, -4);
            $nextSequence = $lastNumber + 1;
        } else {
            $nextSequence = 1;
        }

        $number = 'SL-' . $today . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

        return view('sales.create', compact('inventories', 'number'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|unique:sales,number',
            'date'   => 'required|date',
            'items'  => 'required|array',
            'items.*.inventory_id' => 'required|exists:inventories,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $sale = Sales::create([
                'number'  => $request->number,
                'date'    => $request->date,
                'user_id' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $inventory = Inventories::lockForUpdate()->find($item['inventory_id']);

                if ($inventory->stock < $item['qty']) {
                    throw new \Exception("Stok barang {$inventory->name} tidak cukup!");
                }

                $inventory->decrement('stock', $item['qty']);

                SalesDetails::create([
                    'sales_id'     => $sale->id,
                    'inventory_id' => $item['inventory_id'],
                    'qty'          => $item['qty'],
                    'price'        => $inventory->price,
                ]);
            }

            DB::commit();
            return response()->json(['success' => 'Transaksi Penjualan Berhasil Disimpan!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $sale = Sales::with('details')->findOrFail($id);

            if (Auth::user()->role === Role::SALES && $sale->user_id !== Auth::id()) {
                return response()->json(['error' => 'Anda tidak berhak menghapus data ini.'], 403);
            }

            foreach ($sale->details as $detail) {
                Inventories::where('id', $detail->inventory_id)
                    ->increment('stock', $detail->qty);
            }

            $sale->delete();

            DB::commit();
            return response()->json(['success' => 'Transaksi berhasil dibatalkan dan stok telah dikembalikan.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $sale = Sales::with(['details.inventory', 'user'])->findOrFail($id);
        return response()->json($sale);
    }
}
