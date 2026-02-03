<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'icon',
        'route_name',
        'group',
        'is_enabled',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * التحقق من حالة ميزة معينة
     */
    public static function isEnabled(string $name): bool
    {
        return Cache::remember("feature_{$name}", 3600, function () use ($name) {
            $feature = self::where('name', $name)->first();
            return $feature ? $feature->is_enabled : true;
        });
    }

    /**
     * تفعيل ميزة
     */
    public function enable(): void
    {
        $this->update(['is_enabled' => true]);
        Cache::forget("feature_{$this->name}");
    }

    /**
     * تعطيل ميزة
     */
    public function disable(): void
    {
        $this->update(['is_enabled' => false]);
        Cache::forget("feature_{$this->name}");
    }

    /**
     * الحصول على المميزات المفعلة
     */
    public static function enabled()
    {
        return self::where('is_enabled', true)->orderBy('sort_order')->get();
    }

    /**
     * الحصول على المميزات المعطلة
     */
    public static function disabled()
    {
        return self::where('is_enabled', false)->orderBy('sort_order')->get();
    }

    /**
     * الحصول على المميزات حسب المجموعة
     */
    public static function byGroup(string $group)
    {
        return self::where('group', $group)->orderBy('sort_order')->get();
    }

    /**
     * مسح كاش كل المميزات
     */
    public static function clearAllCache(): void
    {
        $features = self::all();
        foreach ($features as $feature) {
            Cache::forget("feature_{$feature->name}");
            Cache::forget("feature_data_{$feature->name}");
        }
    }
}
