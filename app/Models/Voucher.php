<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:0',
        'max_discount_amount' => 'decimal:0',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function usages()
    {
        return $this->hasMany(VoucherUsage::class);
    }

    public function isAvailableForUser(?int $userId, int $subtotal): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && now()->gt($this->ends_at)) {
            return false;
        }

        if ($subtotal < (int) $this->min_order_amount) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($userId && $this->usage_limit_per_user !== null) {
            $usedByUser = $this->usages()->where('user_id', $userId)->count();
            if ($usedByUser >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount(int $subtotal): int
    {
        if ($subtotal <= 0) {
            return 0;
        }

        if ($this->type === 'percent') {
            $discount = (int) floor($subtotal * ((float) $this->value / 100));
            if ($this->max_discount_amount !== null) {
                $discount = min($discount, (int) $this->max_discount_amount);
            }
        } else {
            $discount = (int) $this->value;
        }

        return max(0, min($discount, $subtotal));
    }
}
