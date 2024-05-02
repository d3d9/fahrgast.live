<?php
declare(strict_types=1);

namespace App\Enum;

use App\Interfaces\IconEnumInterface;

enum Business: int implements IconEnumInterface
{
    // Fallback
    case PRIVATE = 0;
    // MID
    case COMMUTE = 2; // mid das erste: Arbeit
    case BUSINESS = 1; // mid das zweite: dienstlich
    case EDUCATION = 3;
    case SHOPPING = 4;
    case ERRAND = 5;
    case LEISURE = 6;
    case OTHER = 9;

    public function faIcon(): string {
        return match ($this) {
            self::PRIVATE  => 'fa-user',
            self::BUSINESS => 'fa-briefcase',
            self::COMMUTE  => 'fa-building',
            default => ''
        };
    }

    public function title(): string {
        return match ($this) {
            self::PRIVATE  => __('stationboard.business.private'),
            self::BUSINESS => __('stationboard.business.business'),
            self::COMMUTE  => __('stationboard.business.commute'),
            self::EDUCATION => __('stationboard.business.education'),
            self::SHOPPING => __('stationboard.business.shopping'),
            self::ERRAND => __('stationboard.business.errand'),
            self::LEISURE => __('stationboard.business.leisure'),
            self::OTHER => __('stationboard.business.other'),
            default => $this->name
        };
    }

    public function description(): string {
        return match ($this) {
            self::PRIVATE  => '',
            self::BUSINESS => __('stationboard.business.business.detail'),
            self::COMMUTE  => __('stationboard.business.commute.detail'),
            default => $this->name
        };
    }
}
