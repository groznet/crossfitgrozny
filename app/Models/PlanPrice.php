<?php

namespace App\Models;

use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Model;

class PlanPrice extends Model
{
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
