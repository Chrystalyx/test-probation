<?php

namespace App\Models;

use App\Helpers\ModelHelper;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

/**
 * @property int $created_at
 * @property int $qty
 * @property int $updated_at
 */
class PurchaseDetails extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'purchase_details';

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
        'inventory_id',
        'price',
        'purchase_id',
        'qty',
        'updated_at'
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
        'qty' => 'int',
        'price' => 'int',
        'updated_at' => 'datetime'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
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

    public static function mapSchema($params = [], $user = [])
    {
        $model = new self;

        return [
            'field' => [
                'id' => ['column' => $model->table . '.id', 'alias' => 'id', 'type' => 'int'],
                'purchase_id' => ['column' => $model->table . '.purchase_id', 'alias' => 'purchase_id', 'type' => 'int'],
                'inventory_id' => ['column' => $model->table . '.inventory_id', 'alias' => 'inventory_id', 'type' => 'int'],
                'qty' => ['column' => $model->table . '.qty', 'alias' => 'qty', 'type' => 'int'],
                'price' => ['column' => $model->table . '.price', 'alias' => 'price', 'type' => 'int'],
                'inventory_name' => ['column' => 'inventories.name', 'alias' => 'inventory_name', 'type' => 'string'],
                'inventory_code' => ['column' => 'inventories.code', 'alias' => 'inventory_code', 'type' => 'string'],
                'created_at' => ['column' => $model->table . '.created_at', 'alias' => 'created_at', 'type' => 'date'],
                'updated_at' => ['column' => $model->table . '.updated_at', 'alias' => 'updated_at', 'type' => 'date'],
            ],
            'join' => [
                [
                    'table' => 'inventories',
                    'type' => 'left',
                    'on' => ['inventories.id', '=', $model->table . '.inventory_id']
                ]
            ],
            'where' => []
        ];
    }

    public static function datatables($start, $length, $order, $dir, $search, $filter = [])
    {
        $schema = self::mapSchema();

        $totalData = self::count();

        $qry = ModelHelper::select($schema['field'], null, __CLASS__);
        ModelHelper::join($schema['join'], null, $qry);

        $totalFiltered = $qry->count();

        if (empty($search)) {
            if ($length > 0) {
                $qry->skip($start)->take($length);
            }

            foreach ($order as $row) {
                $qry->orderBy($row['column'], $row['dir']);
            }
        } else {
            foreach (array_values($schema['field']) as $key => $val) {
                if ($key < 1) {
                    $qry->whereRaw('(CAST(' . $val['column'] . ' AS CHAR) LIKE \'%' . $search . '%\'');
                } else if (count(array_values($schema['field'])) == ($key + 1)) {
                    $qry->orWhereRaw('CAST(' . $val['column'] . ' AS CHAR) LIKE \'%' . $search . '%\')');
                } else {
                    $qry->orWhereRaw('CAST(' . $val['column'] . ' AS CHAR) LIKE \'%' . $search . '%\'');
                }
            }

            $totalFiltered = $qry->count();

            if ($length > 0) {
                $qry->skip($start)->take($length);
            }

            foreach ($order as $row) {
                $qry->orderBy($row['column'], $row['dir']);
            }
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

        return response()->json($db->first());
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

        if (isset($params['id']) && $params['id']) {
            self::where('id', $params['id'])->update($params);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully Updated Data',
                'data' => self::getById($params['id'])->original
            ]);
        }

        $save = self::create($params);

        DB::commit();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully Added Data',
            'data' => self::getById($save->id)->original
        ]);
    }

    public static function deleteById($id, $params, $request)
    {
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
