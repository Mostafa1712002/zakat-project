<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionWithdrawal extends Model
{
    protected $fillable = [
        'sales_rep_id',
        'expense_id',
        'year',
        'month',
        'amount',
        'withdrawn_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(SalesRep::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function withdrawnByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'withdrawn_by');
    }

    /**
     * التحقق من أن العمولة لم تُسحب من قبل
     */
    public static function isAlreadyWithdrawn(int $salesRepId, int $year, int $month): bool
    {
        return self::where('sales_rep_id', $salesRepId)
            ->where('year', $year)
            ->where('month', $month)
            ->exists();
    }
}
