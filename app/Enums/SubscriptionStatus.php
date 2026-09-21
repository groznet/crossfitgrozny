<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Expiring = 'expiring';
    case Expired = 'expired';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('members.status_active'),
            self::Expiring => __('members.status_expiring'),
            self::Expired => __('members.status_expired'),
            self::None => __('members.status_none'),
        };
    }

    /**
     * Tailwind classes for the status badge.
     */
    public function colorClass(): string
    {
        return match ($this) {
            self::Active => 'bg-green-100 text-green-800',
            self::Expiring => 'bg-yellow-100 text-yellow-800',
            self::Expired => 'bg-red-100 text-red-800',
            self::None => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * Sort priority for the member list: expired/expiring surface first.
     */
    public function sortPriority(): int
    {
        return match ($this) {
            self::Expired => 0,
            self::Expiring => 1,
            self::Active => 2,
            self::None => 3,
        };
    }
}
