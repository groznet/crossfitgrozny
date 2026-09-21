<?php

namespace App\Models;

use App\Enums\PlanType;
use Database\Factories\PlanPriceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPrice extends Model
{
    /** @use HasFactory<PlanPriceFactory> */
    use HasFactory;

    protected $fillable = [
        'plan',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'plan' => PlanType::class,
        ];
    }
}
