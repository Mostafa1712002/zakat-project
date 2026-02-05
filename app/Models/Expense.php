<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'expense_number',
        'expense_category_id',
        'branch_id',
        'user_id',
        'sales_rep_id',
        'employee_id',
        'partner_id',
        'employee_transaction_id',
        'partner_transaction_id',
        'expense_date',
        'title',
        'description',
        'amount',
        'tax_amount',
        'total_amount',
        'payment_method',
        'expense_payment_method_id',
        'reference_number',
        'vendor_name',
        'receipt_image',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(ExpensePaymentMethod::class, 'expense_payment_method_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(SalesRep::class);
    }

    public function employeeTransaction(): BelongsTo
    {
        return $this->belongsTo(EmployeeTransaction::class);
    }

    public function partnerTransaction(): BelongsTo
    {
        return $this->belongsTo(PartnerTransaction::class);
    }

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeInBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }

    public function calculateTotal(): void
    {
        $this->total_amount = $this->amount + $this->tax_amount;
    }

    public function approve(int $userId): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }

    public function reject(): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->save();
    }

    public function markAsPaid(): void
    {
        $this->status = self::STATUS_PAID;
        $this->save();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public static function generateExpenseNumber(): string
    {
        $prefix = 'EXP-' . date('Ym');
        $last = self::withTrashed()
            ->where('expense_number', 'like', $prefix . '-%')
            ->orderBy('expense_number', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->expense_number, -4);
            return $prefix . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '-0001';
    }
}
