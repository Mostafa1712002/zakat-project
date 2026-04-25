<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Service — catalog entry under a ServiceType, with no fixed price.
 *
 * Per requirements (US-002): the price is entered per invoice line item; the
 * catalog only describes the service. ZATCA classification defaults to 'S'
 * (standard rate); 'Z' for zero-rated and 'E' for exempt.
 *
 * Reference: .kiro/specs/ammrk-platform/design.md (Catalog)
 *            .kiro/specs/ammrk-platform/requirements.md (US-002)
 */
class Service extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'service_type_id',
        'name',
        'unit_id',
        'description',
        'default_zatca_classification',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
