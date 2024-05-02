<?php

namespace App\Enum;

enum TravelChainFinished: string
{
    case ARRIVED        = 'arrived';
    case ARRIVED_DIFF_DEST = 'arrived_diff_dest';
    case ABORTED_DIFF_MODE = 'aborted_diff_mode';
    case ABORTED = 'aborted';
    case OTHER = 'other';

    public function getReason(): string {
        return __(sprintf('travelchainfinished.%s', $this->value));
    }

    public function isArrived(): bool {
        return $this === static::ARRIVED || $this === static::ARRIVED_DIFF_DEST;
    }
}
