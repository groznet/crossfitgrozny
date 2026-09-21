<?php

namespace App\Models;

use App\Enums\PlanType;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'member_id',
        'plan',
        'amount',
        'paid_at',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'plan' => PlanType::class,
            'paid_at' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
