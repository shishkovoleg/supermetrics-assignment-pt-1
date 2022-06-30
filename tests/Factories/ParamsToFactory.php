<?php


namespace Tests\Factories;

use DateTime;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;

class ParamsToFactory
{
    public static function make(): ParamsTo
    {
        return (new ParamsTo())
            ->setStatName(StatsEnum::AVERAGE_POST_NUMBER_PER_USER);
    }

    public static function makeWithRange(string $startDate, string $endDate): ParamsTo
    {
        return (new ParamsTo())
            ->setStatName(StatsEnum::AVERAGE_POST_NUMBER_PER_USER)
            ->setStartDate(new DateTime($startDate))
            ->setEndDate(new DateTime($endDate));
    }
}