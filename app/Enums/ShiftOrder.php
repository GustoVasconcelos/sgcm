<?php

namespace App\Enums;

enum ShiftOrder: int
{
    case MORNING = 1;
    case AFTERNOON = 2;
    case NIGHT = 3;
    case DAWN = 4;
    case OFF = 5;

    public function label(): string
    {
        return match($this) {
            self::MORNING => 'Manhã',
            self::AFTERNOON => 'Tarde',
            self::NIGHT => 'Noite',
            self::DAWN => 'Madrugada',
            self::OFF => 'Folga',
        };
    }
}
