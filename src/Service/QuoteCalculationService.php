<?php declare(strict_types=1);

namespace App\Service;

use App\Strategy\QuoteCalculationStrategyInterface;

class QuoteCalculationService
{
    /**
     * @param QuoteCalculationStrategyInterface[] $strategies
     */
    public function __construct(private iterable $strategies)
    {
    }

    /**
     * Calculates quotas for each supplier.
     *
     * @param array<string, int> $teacherTopics
     * @param array<string, array<string>> $matches
     * @return array<string, float>
     */
    public function calculateQuotes(array $teacherTopics, array $matches): array
    {
        $quotes = [];
        foreach ($matches as $provider => $matchedTopics) {
            $quotes[$provider] = $this->getQuoteForProvider($teacherTopics, $matchedTopics);
        }
        return array_filter($quotes);
    }

    /**
     * Calculates the quote for a single provider.
     *
     * @param array<string, int> $teacherTopics
     * @param array<string> $matchedTopics
     * @return float|null
     */
    private function getQuoteForProvider(array $teacherTopics, array $matchedTopics): ?float
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($matchedTopics)) {
                $quote = $strategy->calculate($teacherTopics, $matchedTopics);
                return $quote !== null ? round($quote, 2) : null;
            }
        }
        return null;
    }
}
