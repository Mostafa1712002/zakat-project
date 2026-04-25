<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Unit — measurement unit for services (يوم, ساعة, باقة, ...).
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Catalog)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 3.1, 3.2)
 */
class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
