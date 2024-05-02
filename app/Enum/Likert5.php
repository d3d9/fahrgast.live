<?php

namespace App\Enum;

enum Likert5: int
{
    case LEAST = 1;
    case LESS = 2;
    case MIDDLE = 3;
    case MORE = 4;
    case MOST = 5;

    public function getLabel(?string $labelType = NULL): string {
        if ($labelType === NULL) return strval(this->value);
        return __(sprintf('likert5.%s.%s', $labelType, $this->value));
    }
}
