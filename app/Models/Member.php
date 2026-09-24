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

    public function firstPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->oldestOfMany(['paid_at', 'id']);
    }

    /**
     * When the member joined: their first payment date, or when their row
     * was created if they haven't paid yet. Never moves on profile edits.
     */
    protected function joinedAt(): Attribute
    {
        return Attribute::make(get: fn () => $this->firstPayment?->paid_at ?? $this->created_at);
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
     *
     * Members saved without a token (e.g. seeded with model events off)
     * get one generated on first use instead of breaking the admin page.
     */
    public function publicUrl(): string
    {
        if ($this->public_token === null) {
            $this->forceFill(['public_token' => Str::random(32)])->saveQuietly();
        }

        return route('public.member.show', $this->public_token);
    }

    /**
     * The short, shareable public profile link — /u/{username} once the
     * member has picked one, /u/{id} until then.
     */
    public function profileUrl(): string
    {
        return route('public.profile.show', $this->username ?? $this->id);
    }
}
