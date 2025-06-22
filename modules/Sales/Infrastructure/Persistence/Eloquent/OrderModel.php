<?php

namespace Modules\Sales\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OrderModel extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'orders';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        // Add other fillable attributes here
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        // Add hidden attributes here
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        // Add attribute casts here
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}