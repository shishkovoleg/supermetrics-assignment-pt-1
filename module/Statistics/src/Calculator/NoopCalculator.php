<?php

declare(strict_types = 1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class NoopCalculator extends AbstractCalculator
{
    protected const UNITS = 'posts';

    private array $totals = [];

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        // if we are sure that authorName is unique field
        // we can use authorName
        // since I'm not sure in that, I use less human readable IDs
        $authorId = $postTo->getAuthorId();

        if (array_key_exists($authorId, $this->totals)) {
            $this->totals[$authorId]++;
        } else {
            $this->totals[$authorId] = 1;
        }
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        $stats = new StatisticsTo();

        foreach ($this->totals as $userId => $total) {
            $perUserPerMonth = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($userId)
                ->setValue($total)
                ->setUnits(self::UNITS);

            $stats->addChild($perUserPerMonth);
        }

        return $stats;
    }
}
