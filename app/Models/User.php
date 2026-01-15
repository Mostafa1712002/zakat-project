<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'branch_id',
        'phone',
        'is_active',
        'is_super_admin',
    ];

    /**
     * التحقق من كون المستخدم مسؤول أعلى
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * المندوب المرتبط بالمستخدم
     */
    public function salesRep(): HasOne
    {
        return $this->hasOne(SalesRep::class);
    }

    /**
     * التحقق من كون المستخدم مندوب مبيعات
     */
    public function isSalesRep(): bool
    {
        return $this->salesRep()->exists() || $this->hasRole('sales_rep');
    }

    /**
     * الحصول على معرف المندوب
     */
    public function getSalesRepIdAttribute(): ?int
    {
        return $this->salesRep?->id;
    }

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
}
