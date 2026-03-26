<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['key', 'value', 'tenant_id'];

    /**
     * Retrieve a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $tenantId = app()->has('currentTenant') ? app('currentTenant')->id : (auth()->hasUser() ? auth()->user()->tenant_id : 'global');
        
        // Cache the setting to avoid repeated DB hits, but make it tenant-specific
        return Cache::rememberForever("tenant_{$tenantId}_app_setting_{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set or update a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        $tenantId = app()->has('currentTenant') ? app('currentTenant')->id : (auth()->hasUser() ? auth()->user()->tenant_id : 'global');
        
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("tenant_{$tenantId}_app_setting_{$key}");
    }
}
