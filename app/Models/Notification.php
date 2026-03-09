<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;
use App\Traits\BelongsToTenant;

class Notification extends DatabaseNotification
{
    use BelongsToTenant;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // If tenant_id is missing, try to infer it from the notifiable user
            if (!$model->tenant_id && $model->notifiable_type === \App\Models\User::class) {
                $user = \App\Models\User::find($model->notifiable_id);
                if ($user && $user->tenant_id) {
                    $model->tenant_id = $user->tenant_id;
                }
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'type',
        'data',
        'read_at',
        'tenant_id',
    ];
}
