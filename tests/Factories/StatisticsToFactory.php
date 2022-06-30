<?php

declare(strict_types = 1);

namespace Tests\Factories;

use Statistics\Dto\StatisticsTo;
use Statistics\Enum\StatsEnum;

class StatisticsToFactory
{
    private const UNITS = 'posts';

    public static function make(): StatisticsTo
    {
        return (new StatisticsTo())
            ->setName(StatsEnum::AVERAGE_POST_NUMBER_PER_USER)
            ->setUnits(self::UNITS);
    }
}