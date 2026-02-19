<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $failed_job_ids
 * @property string $name
 * @property string $options
 * @property int    $cancelled_at
 * @property int    $created_at
 * @property int    $failed_jobs
 * @property int    $finished_at
 * @property int    $pending_jobs
 * @property int    $total_jobs
 */
class JobBatches extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'job_batches';

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
        'cancelled_at', 'created_at', 'failed_job_ids', 'failed_jobs', 'finished_at', 'name', 'options', 'pending_jobs', 'total_jobs'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string', 'cancelled_at' => 'int', 'created_at' => 'int', 'failed_job_ids' => 'string', 'failed_jobs' => 'int', 'finished_at' => 'int', 'name' => 'string', 'options' => 'string', 'pending_jobs' => 'int', 'total_jobs' => 'int'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    // Scopes...

    // Functions ...

    // Relations ...
}
