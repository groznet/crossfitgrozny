<?php

namespace App\Enums;

enum MemberStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('members.member_status_pending'),
            self::Active => __('members.member_status_active'),
            self::Archived => __('members.member_status_archived'),
        };
    }
}
