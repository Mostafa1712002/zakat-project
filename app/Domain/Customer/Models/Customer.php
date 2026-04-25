<?php

namespace App\Domain\Customer\Models;

use App\Domain\Customer\Scopes\AccountManagerScope;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Customer — Phase 4 ZATCA-aware customer record.
 *
 * Account Manager scoping is enforced via the global AccountManagerScope:
 * users with only the `Account Manager` role see only their own customers.
 *
 * Reference: .kiro/specs/ammrk-platform/requirements.md (US-010, US-011)
 *            .kiro/specs/ammrk-platform/design.md (Customers schema)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 4.1)
 */
class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_COMPANY = 'company';
    public const TYPE_GOVERNMENT = 'government';
    public const TYPE_INDIVIDUAL = 'individual';

    protected $fillable = [
        'name',
        'type',
        'vat_number',
        'cr_number',
        'phone',
        'email',
        'is_tax_exempt',
        'account_manager_id',
        'branch_id',
        'street_name',
        'building_number',
        'secondary_number',
        'district',
        'city',
        'postal_code',
        'country_code',
        'notes',
    ];

    protected $casts = [
        'is_tax_exempt' => 'boolean',
    ];

    protected $attributes = [
        'country_code' => 'SA',
        'type' => self::TYPE_COMPANY,
        'is_tax_exempt' => false,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new AccountManagerScope());
    }

    public function accountManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function primaryContact(): HasMany
    {
        return $this->hasMany(CustomerContact::class)->where('is_primary', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('vat_number', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }
}
