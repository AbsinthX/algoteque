<?php

namespace App\Tests\Service;

use App\Service\QuoteCalculationService;
use App\Strategy\QuoteCalculationStrategyInterface;
use PHPUnit\Framework\TestCase;

class QuoteCalculationServiceTest extends TestCase
{
    /**
     * Tests calculating quotes with a supported strategy.
     */
    public function testCalculateQuotesWithSupportedStrategy(): void
    {
        $strategyMock = $this->createMock(QuoteCalculationStrategyInterface::class);
        $strategyMock->method('supports')->willReturn(true);
        $strategyMock->method('calculate')->willReturn(8.0);

        $service = new QuoteCalculationService([$strategyMock]);

        $teacherTopics = ['math' => 50, 'science' => 30];
        $matches = ['provider_a' => ['math']];
        $result = $service->calculateQuotes($teacherTopics, $matches);

        $this->assertEquals(['provider_a' => 8], $result);
    }

    /**
     * Tests calculating quotes with no supported strategies.
     */
    public function testCalculateQuotesWithNoSupportedStrategies(): void
    {
        $strategyMock = $this->createMock(QuoteCalculationStrategyInterface::class);
        $strategyMock->method('supports')->willReturn(false);

        $service = new QuoteCalculationService([$strategyMock]);

        $teacherTopics = ['math' => 50, 'science' => 30];
        $matches = ['provider_a' => ['reading']];
        $result = $service->calculateQuotes($teacherTopics, $matches);

        $this->assertEquals([], $result);
    }

    /**
     * Tests calculating quotes with multiple providers.
     */
    public function testCalculateQuotesWithMultipleProviders(): void
    {
        $strategyMock1 = $this->createMock(QuoteCalculationStrategyInterface::class);
        $strategyMock1->expects($this->any())
            ->method('supports')
            ->willReturnCallback(fn($topics) => in_array('math', $topics));

        $strategyMock1->method('calculate')->willReturn(10.0);

        $strategyMock2 = $this->createMock(QuoteCalculationStrategyInterface::class);
        $strategyMock2->expects($this->any())
            ->method('supports')
            ->willReturnCallback(fn($topics) => in_array('science', $topics));

        $strategyMock2->method('calculate')->willReturn(5.0);

        $service = new QuoteCalculationService([$strategyMock1, $strategyMock2]);

        $teacherTopics = ['math' => 50, 'science' => 20, 'art' => 10];
        $matches = [
            'provider_a' => ['math'],
            'provider_b' => ['science'],
        ];
        $result = $service->calculateQuotes($teacherTopics, $matches);

        $this->assertEquals(['provider_a' => 10.0, 'provider_b' => 5.0], $result);
    }

    /**
     * Tests filtering out null quotes during calculation.
     */
    public function testCalculateQuotesFiltersOutNullQuotes(): void
    {
        $strategyMock = $this->createMock(QuoteCalculationStrategyInterface::class);
        $strategyMock->method('supports')->willReturn(true);
        $strategyMock->method('calculate')->willReturn(null);

        $service = new QuoteCalculationService([$strategyMock]);

        $teacherTopics = ['math' => 50, 'science' => 30];
        $matches = ['provider_a' => ['reading']];
        $result = $service->calculateQuotes($teacherTopics, $matches);

        $this->assertEquals([], $result);
    }

    /**
     * Tests rounding quotes to two decimal places.
     */
    public function testCalculateQuotesRoundsQuotesToTwoDecimals(): void
    {
        $strategyMock = $this->createMock(QuoteCalculationStrategyInterface::class);
        $strategyMock->method('supports')->willReturn(true);
        $strategyMock->method('calculate')->willReturn(2.475);

        $service = new QuoteCalculationService([$strategyMock]);

        $teacherTopics = ['math' => 50, 'science' => 9.9];
        $matches = ['provider_a' => ['science']];
        $result = $service->calculateQuotes($teacherTopics, $matches);

        $this->assertEquals(['provider_a' => 2.48], $result);
    }

    /**
     * Tests getQuoteForProvider with a supported strategy that returns a valid quote.
     */
    public function testGetQuoteForProviderWithSupportedStrategy(): void
    {
        $strategyMock = $this->createMock(QuoteCalculationStrategyInterface::class);
        $strategyMock->method('supports')->willReturn(true);
        $strategyMock->method('calculate')->willReturn(12.345);

        $service = new QuoteCalculationService([$strategyMock]);

        $teacherTopics = ['math' => 50];
        $matchedTopics = ['math'];
        $result = (new \ReflectionMethod($service, 'getQuoteForProvider'))
            ->invokeArgs($service, [$teacherTopics, $matchedTopics]);

        $this->assertEquals(12.35, $result);
    }

    /**
     * Tests getQuoteForProvider when no strategies support the topics.
     */
    public function testGetQuoteForProviderWithNoSupportedStrategy(): void
    {
        $strategyMock = $this->createMock(QuoteCalculationStrategyInterface::class);
        $strategyMock->method('supports')->willReturn(false);

        $service = new QuoteCalculationService([$strategyMock]);

        $teacherTopics = ['history' => 25];
        $matchedTopics = ['reading'];
        $result = (new \ReflectionMethod($service, 'getQuoteForProvider'))
            ->invokeArgs($service, [$teacherTopics, $matchedTopics]);

        $this->assertNull($result);
    }

    /**
     * Tests getQuoteForProvider when the calculated quote is null.
     */
    public function testGetQuoteForProviderWithNullQuote(): void
    {
        $strategyMock = $this->createMock(QuoteCalculationStrategyInterface::class);
        $strategyMock->method('supports')->willReturn(true);
        $strategyMock->method('calculate')->willReturn(null);

        $service = new QuoteCalculationService([$strategyMock]);

        $teacherTopics = ['math' => 30, 'art' => 20];
        $matchedTopics = ['art'];
        $result = (new \ReflectionMethod($service, 'getQuoteForProvider'))
            ->invokeArgs($service, [$teacherTopics, $matchedTopics]);

        $this->assertNull($result);
    }

    /**
     * Tests getQuoteForProvider ensures rounding of the result to two decimal places.
     */
    public function testGetQuoteForProviderRoundsResult(): void
    {
        $strategyMock = $this->createMock(QuoteCalculationStrategyInterface::class);
        $strategyMock->method('supports')->willReturn(true);
        $strategyMock->method('calculate')->willReturn(99.999);

        $service = new QuoteCalculationService([$strategyMock]);

        $teacherTopics = ['science' => 40];
        $matchedTopics = ['science'];
        $result = (new \ReflectionMethod($service, 'getQuoteForProvider'))
            ->invokeArgs($service, [$teacherTopics, $matchedTopics]);

        $this->assertEquals(100.0, $result);
    }
}