<?php

namespace App\Models;

use App\Enums\PlanType;
use App\Enums\ReceiptStatus;
use Database\Factories\PaymentReceiptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceipt extends Model
{
    /** @use HasFactory<PaymentReceiptFactory> */
    use HasFactory;

    protected $fillable = [
        'member_id',
        'plan',
        'amount',
        'file_path',
        'status',
        'payment_id',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'plan' => PlanType::class,
            'status' => ReceiptStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isPdf(): bool
    {
        return str_ends_with(strtolower($this->file_path), '.pdf');
    }
}
