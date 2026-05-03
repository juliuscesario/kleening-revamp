<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes; // <-- TAMBAHKAN SoftDeletes DI SINI

    /**
     * Resolve the route binding to prevent SQL errors for non-numeric IDs in PostgreSQL.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null || $field === $this->getKeyName()) {
            if (!is_numeric($value)) {
                return null;
            }
        }
        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone_number',
        'password',
        'role',
        'area_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }
}
