<?php

namespace App\Enums;

enum Badge: string
{
    case TheCarrier   = 'the_carrier';
    case TheCatalyst  = 'the_catalyst';
    case SoloPeak     = 'solo_peak';
    case TheStrategist = 'the_strategist';
    case None         = 'none';

    public function label(): string
    {
        return match ($this) {
            self::TheCarrier    => 'The Carrier',
            self::TheCatalyst   => 'The Catalyst',
            self::SoloPeak      => 'Solo Peak',
            self::TheStrategist => 'The Strategist',
            self::None          => 'Climber',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::TheCarrier    => 'Reached the Summit with the team\'s trust. A true leader who carried others upward.',
            self::TheCatalyst   => 'Did not summit, but became the team\'s backbone. Others succeeded because of your sacrifice.',
            self::SoloPeak      => 'Reached the Summit alone. Capable, but at what cost?',
            self::TheStrategist => 'Most versatile leader — demonstrated the widest range of leadership behaviors.',
            self::None          => 'Still climbing. Every summit begins with the first step.',
        };
    }
}