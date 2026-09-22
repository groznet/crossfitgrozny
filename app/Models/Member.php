<?php

namespace App\Models;

use App\Enums\MemberStatus;
use App\Enums\PreferredTime;
use App\Enums\SubscriptionStatus;
use App\Support\PhoneNumber;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone',
        'photo_url',
        'birth_date',
        'preferred_time',
        'note',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            $member->public_token ??= Str::random(32);
        });
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'preferred_time' => PreferredTime::class,
            'status' => MemberStatus::class,
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany(['paid_at', 'id']);
    }

    protected function subscriptionStatus(): Attribute
    {
        return Attribute::make(get: function () {
            $validUntil = $this->latestPayment?->valid_until;

            if (! $validUntil) {
                return SubscriptionStatus::None;
            }

            $daysLeft = today(config('app.timezone'))->diffInDays($validUntil, false);

            return match (true) {
                $daysLeft < 0 => SubscriptionStatus::Expired,
                $daysLeft <= 5 => SubscriptionStatus::Expiring,
                default => SubscriptionStatus::Active,
            };
        });
    }

    public function whatsappUrl(): string
    {
        return PhoneNumber::toWhatsAppUrl($this->phone);
    }

    /**
     * The unguessable link a member can use to view their own status —
     * nothing stops them sharing it, but there's no directory listing or
     * other way to discover another member's link.
     */
    public function publicUrl(): string
    {
        return route('public.member.show', $this->public_token);
    }
}
