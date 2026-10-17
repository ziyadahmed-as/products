<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = self::find($key);
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value)
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getLogoUrl(): string
    {
        $logo = self::get('site_logo');
        if ($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo)) {
            return asset('storage/' . $logo);
        }
        if ($logo && file_exists(public_path($logo))) {
            return asset($logo);
        }
        if (file_exists(public_path('images/logo.jpg'))) {
            return asset('images/logo.jpg');
        }
        if (file_exists(public_path('images/logo.png'))) {
            return asset('images/logo.png');
        }
        return asset('images/logo.jpg');
    }

    public static function getSiteName(): string
    {
        return self::get('site_name', config('app.name', 'Albareck'));
    }
}
