<?php

namespace App\Models;

use App\Enums\Role;
use App\Helpers\ModelHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * @property int    $created_at
 * @property int    $updated_at
 * @property string $name
 * @property string $password
 * @property string $role
 * @property string $username
 */
class Users extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

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
        'name',
        'password',
        'role',
        'updated_at',
        'username'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'timestamp',
        'name' => 'string',
        'password' => 'hashed',
        'role' => Role::class,
        'updated_at' => 'timestamp',
        'username' => 'string'
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
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchases::class, 'user_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class, 'user_id');
    }

    public function hasRole(Role $role): bool
    {
        return $this->role === $role;
    }

    public static function mapSchema($params = [], $user = [])
    {
        $model = new self;

        return [
            'field' => [
                'id' => ['column' => $model->table . '.id', 'alias' => 'id', 'type' => 'int'],
                'name' => ['column' => $model->table . '.name', 'alias' => 'name', 'type' => 'string'],
                'username' => ['column' => $model->table . '.username', 'alias' => 'username', 'type' => 'string'],
                'role' => ['column' => $model->table . '.role', 'alias' => 'role', 'type' => 'string'],
                'created_at' => ['column' => $model->table . '.created_at', 'alias' => 'created_at', 'type' => 'date'],
                'updated_at' => ['column' => $model->table . '.updated_at', 'alias' => 'updated_at', 'type' => 'date'],
            ],
            'join' => [],
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
