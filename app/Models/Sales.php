<?php

namespace App\Models;

use App\Helpers\ModelHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

/**
 * @property int    $created_at
 * @property int    $updated_at
 * @property Date   $date
 * @property string $number
 */
class Sales extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sales';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_at',
        'date',
        'number',
        'updated_at',
        'user_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'date' => 'date:Y-m-d',
        'number' => 'string',
        'updated_at' => 'datetime'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'date',
        'updated_at'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    // Scopes...

    // Functions ...

    // Relations ...
    public function details(): HasMany
    {
        return $this->hasMany(SalesDetails::class, 'sales_id');
    }

    public static function mapSchema($params = [], $user = [])
    {
        $model = new self;

        return [
            'field' => [
                'id' => ['column' => $model->table . '.id', 'alias' => 'id', 'type' => 'int'],
                'number' => ['column' => $model->table . '.number', 'alias' => 'number', 'type' => 'string'],
                'date' => ['column' => $model->table . '.date', 'alias' => 'date', 'type' => 'date'],
                'user_id' => ['column' => $model->table . '.user_id', 'alias' => 'user_id', 'type' => 'int'],
                'user_name' => ['column' => 'users.name', 'alias' => 'user_name', 'type' => 'string'],
                'created_at' => ['column' => $model->table . '.created_at', 'alias' => 'created_at', 'type' => 'date'],
                'updated_at' => ['column' => $model->table . '.updated_at', 'alias' => 'updated_at', 'type' => 'date'],
            ],
            'join' => [
                [
                    'table' => 'users',
                    'type' => 'left',
                    'on' => ['users.id', '=', $model->table . '.user_id']
                ]
            ],
            'where' => []
        ];
    }

    public static function datatables($start, $length, $order, $dir, $search, $filter = [])
    {
        $schema = self::mapSchema();

        $qry = ModelHelper::select($schema['field'], null, __CLASS__);
        ModelHelper::join($schema['join'], null, $qry);

        $totalData = (clone $qry)->count();

        if (!empty($search)) {
            $qry->where(function ($q) use ($schema, $search) {
                foreach (array_values($schema['field']) as $key => $val) {
                    $q->orWhereRaw('CAST(' . $val['column'] . ' AS CHAR) LIKE \'%' . $search . '%\'');
                }
            });
        }

        $totalFiltered = $qry->count();

        if ($length > 0) {
            $qry->skip($start)->take($length);
        }

        foreach ($order as $row) {
            $qry->orderBy($row['column'], $row['dir']);
        }

        return [
            'data' => $qry->get(),
            'totalData' => $totalData,
            'totalFiltered' => $totalFiltered
        ];
    }

    public static function getPaginatedResult($params, $request)
    {
        $append = [];
        $schema = self::mapSchema();

        $paramsPage = isset($params['page']) ? $params['page'] : 0;

        $or = [];

        unset($params['page']);

        if (isset($params['or']) && $params['or']) {
            $or = $params['or'];
            unset($params['or']);
        }

        $db = ModelHelper::select($schema['field'], $request, __CLASS__);
        ModelHelper::join($schema['join'], $request, $db);

        if ($params) {
            ModelHelper::dynamicFilterAnd($params, $request, $db, __CLASS__);
        }

        if ($or) {
            ModelHelper::dynamicFilterOr($or, $request, $db, __CLASS__);
        }

        $results = ModelHelper::generatePagingResults($schema, $paramsPage, $params, $request, $db, $append);

        return response()->json($results);
    }

    public static function getById($id, $params = [], $request = null)
    {
        $model = new self;

        $schema = self::mapSchema();

        $db = ModelHelper::select($schema['field'], $request, __CLASS__)->where($model->table . '.id', $id);

        ModelHelper::join($schema['join'], $request, $db);

        $result = $db->first();

        if ($result) {
            $result->details = SalesDetails::where('sales_id', $id)
                ->join('inventories', 'inventories.id', '=', 'sales_details.inventory_id')
                ->select('sales_details.*', 'inventories.name as inventory_name', 'inventories.code as inventory_code')
                ->get();
        }

        return response()->json($result);
    }

    public static function getAllResult($params, $request)
    {
        $append = [];
        $schema = self::mapSchema();

        $or = [];

        unset($params['all']);

        if (isset($params['or']) && $params['or']) {
            $or = $params['or'];
            unset($params['or']);
        }

        $db = ModelHelper::select($schema['field'], $request, __CLASS__);
        ModelHelper::join($schema['join'], $request, $db);

        if ($params) {
            ModelHelper::dynamicFilterAnd($params, $request, $db, __CLASS__);
        }

        if ($or) {
            ModelHelper::dynamicFilterOr($or, $request, $db, __CLASS__);
        }

        $results = ModelHelper::generateAllResults($schema, $params, $request, $db, $append);

        return response()->json($results);
    }

    public static function createOrUpdate($params, $method, $request)
    {
        DB::beginTransaction();

        if (isset($params['_token']) && $params['_token']) {
            unset($params['_token']);
        }

        $details = [];
        if (isset($params['details']) && $params['details']) {
            $details = $params['details'];
            unset($params['details']);

            foreach ($details as $detail) {
                $inventory = Inventories::find($detail['inventory_id']);
                if (!$inventory || $inventory->stock < $detail['qty']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Insufficient stock for ' . ($inventory ? $inventory->name : 'Unknown Item')
                    ], 400);
                }
            }
        }

        if (isset($params['id']) && $params['id']) {
            $oldDetails = SalesDetails::where('sales_id', $params['id'])->get();
            foreach ($oldDetails as $oldDetail) {
                Inventories::where('id', $oldDetail->inventory_id)
                    ->increment('stock', $oldDetail->qty);
            }
            SalesDetails::where('sales_id', $params['id'])->delete();

            self::where('id', $params['id'])->update([
                'number' => $params['number'] ?? null,
                'date' => $params['date'] ?? null,
                'user_id' => $params['user_id'] ?? null,
            ]);

            foreach ($details as $detail) {
                SalesDetails::create([
                    'sales_id' => $params['id'],
                    'inventory_id' => $detail['inventory_id'],
                    'qty' => $detail['qty'],
                    'price' => $detail['price'],
                ]);

                Inventories::where('id', $detail['inventory_id'])
                    ->decrement('stock', $detail['qty']);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully Updated Data',
                'data' => self::getById($params['id'])->original
            ]);
        }

        $number = $params['number'] ?? null;
        if ($number === 'Auto Generated' || empty($number)) {
            $count = self::withTrashed()->whereDate('created_at', \Carbon\Carbon::today())->count();
            $number = 'SLS-' . \Carbon\Carbon::now()->format('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        }

        $save = self::create([
            'number' => $number,
            'date' => $params['date'] ?? null,
            'user_id' => $params['user_id'] ?? null,
        ]);

        foreach ($details as $detail) {
            SalesDetails::create([
                'sales_id' => $save->id,
                'inventory_id' => $detail['inventory_id'],
                'qty' => $detail['qty'],
                'price' => $detail['price'],
            ]);

            Inventories::where('id', $detail['inventory_id'])
                ->decrement('stock', $detail['qty']);
        }

        DB::commit();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully Added Data',
            'data' => self::getById($save->id)->original
        ]);
    }

    public static function deleteById($id, $params, $request)
    {
        $details = SalesDetails::where('sales_id', $id)->get();
        foreach ($details as $detail) {
            Inventories::where('id', $detail->inventory_id)
                ->increment('stock', $detail->qty);
        }
        SalesDetails::where('sales_id', $id)->delete();
        self::where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully Deleted Data'
        ]);
    }

    public static function approveById($id, $params, $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully Approved Data',
            'data' => null
        ]);
    }
}
