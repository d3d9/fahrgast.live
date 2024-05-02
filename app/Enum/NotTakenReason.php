<?php

namespace App\Enum;

enum NotTakenReason: string
{
    case MISSED         = 'missed';
    case CANCELLED      = 'cancelled';
    case DELAYED        = 'delayed';
    case EARLY_DEP      = 'early_dep';
    case BETTER_ALTERNATIVE = 'better_alternative';
    case OVERCROWDED    = 'overcrowded';
    case PREV_DEVIATION = 'prev_deviation';
    case ADV_DEVIATION = 'adv_deviation';
    case DIFF_EXIT      = 'diff_exit';
    case OTHER          = 'other';

    public function getReason(): string {
        return __(sprintf('nottakenreason.%s', $this->value));
    }
}
