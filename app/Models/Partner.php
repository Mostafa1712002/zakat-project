<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'partner_code',
        'email',
        'phone',
        'national_id',
        'address',
        'ownership_percentage',
        'initial_investment',
        'join_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'join_date' => 'date',
        'ownership_percentage' => 'decimal:2',
        'initial_investment' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(PartnerTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalWithdrawalsAttribute(): float
    {
        return $this->transactions()
            ->where('type', PartnerTransaction::TYPE_WITHDRAWAL)
            ->sum('amount');
    }

    public function getTotalProfitSharesAttribute(): float
    {
        return $this->transactions()
            ->where('type', PartnerTransaction::TYPE_PROFIT_SHARE)
            ->sum('amount');
    }

    public function getTotalInvestmentsAttribute(): float
    {
        return $this->transactions()
            ->where('type', PartnerTransaction::TYPE_INVESTMENT)
            ->sum('amount') + $this->initial_investment;
    }

    public function getTotalReturnsAttribute(): float
    {
        return $this->transactions()
            ->where('type', PartnerTransaction::TYPE_RETURN)
            ->sum('amount');
    }

    public function getCurrentBalanceAttribute(): float
    {
        $investments = $this->total_investments;
        $returns = $this->total_returns;
        return $investments - $returns;
    }

    public static function generatePartnerCode(): string
    {
        $prefix = 'PTR-';
        $lastPartner = static::withTrashed()->latest('id')->first();
        $nextNumber = $lastPartner ? $lastPartner->id + 1 : 1;
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
