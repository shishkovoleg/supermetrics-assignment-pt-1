<?php

declare(strict_types = 1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use DateTime;
use SocialPost\Dto\SocialPostTo;
use Statistics\Calculator\NoopCalculator;
use Statistics\Dto\ParamsTo;
use Statistics\Dto\StatisticsTo;
use Statistics\Enum\StatsEnum;
use Tests\Factories\SocialPostToFactory;
use Tests\Factories\StatisticsToFactory;
use Tests\Factories\ParamsToFactory;

/**
 * Here we do similar work as in Tests\unit\NoopCalculatorTest class.
 *
 * Difference is that we don't mock AbstractCalculator::checkPost(),
 * instead we want to test integration between NoopCalculator and
 * AbstractCalculator and verify that checkPost() works as we expect.
 *
 * Maybe it's not the best example since here we test integration
 * between a child class and its parent class (I did it to save time),
 * but it still clearly illustrates that we can test 2 and more classes
 * at once as a whole.
 *
 * I didn't write tests for all possible scenarios to save time.
 * */
class NoopCalculatorTest extends TestCase
{
    private const USER_1 = 'user_1';
    private const USER_2 = 'user_2';

    private NoopCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new NoopCalculator();
        $params = $this->buildParams();
        $this->calculator->setParameters($params);
    }

    public function testPostsOutOfDateRangeIgnored()
    {
        $outOfRangePostCollection = $this->getOutOfRangePostCollection();
        $expectedEmptyStats = $this->getEmptyExpectedResult();

        foreach ($outOfRangePostCollection as $post) {
            $this->calculator->accumulateData($post);
        }
        $result = $this->calculator->calculate();

        $this->assertEquals($expectedEmptyStats, $result);
    }

    public function testNotEmptyStatsResult(): void
    {
        $postCollection = $this->getInRangePostCollection();
        $expectedStats = $this->getInRangeExpectedResult();

        foreach ($postCollection as $post) {
            $this->calculator->accumulateData($post);
        }
        $result = $this->calculator->calculate();

        $this->assertEquals($expectedStats, $result);
    }

    private function buildParams(): ParamsTo
    {
        return ParamsToFactory::makeWithRange('2022-06-01', '2022-06-30');
    }

    private function getEmptyExpectedResult(): StatisticsTo
    {
        return StatisticsToFactory::make();
    }

    /**
     * @return array<SocialPostTo>
     */
    private function getOutOfRangePostCollection(): array
    {
        return [
            SocialPostToFactory::makeWithDate(self::USER_1, '2022-01-03'),
            SocialPostToFactory::makeWithDate(self::USER_1, '2022-02-04'),
            SocialPostToFactory::makeWithDate(self::USER_2, '2022-04-07'),
        ];
    }

    /**
     * @return array<SocialPostTo>
     */
    private function getInRangePostCollection(): array
    {
        return [
            SocialPostToFactory::makeWithDate(self::USER_1, '2022-06-03'),
            SocialPostToFactory::makeWithDate(self::USER_1, '2022-06-04'),
            SocialPostToFactory::makeWithDate(self::USER_2, '2022-06-07'),
        ];
    }

    private function getInRangeExpectedResult(): StatisticsTo
    {
        $expectedByUser1 = StatisticsToFactory::make()
            ->setSplitPeriod(self::USER_1)
            ->setValue(2);

        $expectedByUser2 = StatisticsToFactory::make()
            ->setSplitPeriod(self::USER_2)
            ->setValue(1);

        return StatisticsToFactory::make()
            ->addChild($expectedByUser1)
            ->addChild($expectedByUser2);
    }
}