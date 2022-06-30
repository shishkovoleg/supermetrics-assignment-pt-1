<?php

declare(strict_types = 1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use Mockery;
use Statistics\Calculator\NoopCalculator;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;
use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;
use Tests\Factories\StatisticsToFactory;
use Tests\Factories\SocialPostToFactory;

/**
 * Since I had to write implementation details of NoopCalculator,
 * I decided that I must to write unit tests for my solution.
 *
 * Usually we write unit tests for a single function.
 *
 * In this example I've made small exception since $totals property
 * is private and it's not possible to read and write it outside class
 * without PHP Reflection API (one more workaround is to extend NoopCalculator
 * and add to a child class public methods and even a getter and setter,
 * but I don't like this).
 *
 * Therefore I've made a decision to test NoopCalculator as a whole.
 * We can't call it's methods directly since they are protected and
 * not reachable outside of class. Nonetheless we can call public
 * method from its parent class which in turn call protected methods
 * in NoopCalculator class.
 *
 * To avoid side effects of checkPost(), I mocked it.
 * calculate() method adds a small side effect, but I think that
 * here I can go with this tradeoff since it essentially sets
 * properties we have in our NoopCalculator class.
 * */
class NoopCalculatorTest extends TestCase
{
    private const USER_1 = 'user_1';
    private const USER_2 = 'user_2';

    private Mockery\MockInterface|NoopCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = Mockery::mock(NoopCalculator::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $params = $this->buildParams();
        $this->calculator->setParameters($params);
    }

    public function testEmptyStatsResult(): void
    {
        $expectedEmptyStats = $this->getEmptyExpectedResult();

        $result = $this->calculator->calculate();

        $this->assertEquals($expectedEmptyStats, $result);
    }

    public function testNotEmptyStatsResult(): void
    {
        $postCollection = $this->getPostCollection();
        $expectedStats = $this->getExpectedResult();

        $this->expectCheckPostTruthy(3);

        foreach ($postCollection as $post) {
            $this->calculator->accumulateData($post);
        }
        $result = $this->calculator->calculate();

        $this->assertEquals($expectedStats, $result);
    }

    private function buildParams(): ParamsTo
    {
        $params = new ParamsTo();
        $params->setStatName(StatsEnum::AVERAGE_POST_NUMBER_PER_USER);

        return $params;
    }

    private function getEmptyExpectedResult(): StatisticsTo
    {
        return StatisticsToFactory::make();
    }

    private function getExpectedResult(): StatisticsTo
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

    /**
     * @return array<SocialPostTo>
     */
    private function getPostCollection(): array
    {
        return [
            SocialPostToFactory::make(self::USER_1),
            SocialPostToFactory::make(self::USER_1),
            SocialPostToFactory::make(self::USER_2),
        ];
    }

    /**
     * we don't test parent class checkPost method,
     * therefore we mock it to make it always return true
     * */
    private function expectCheckPostTruthy(int $times = 1): void
    {
        $this->calculator
            ->shouldReceive('checkPost')
            ->withAnyArgs()
            ->times($times)
            ->andReturnTrue();
    }
}