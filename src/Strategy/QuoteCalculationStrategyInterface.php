<?php declare(strict_types=1);

namespace App\Strategy;

interface QuoteCalculationStrategyInterface
{
    /**
     * Calculates the amount for matched topics.
     *
     * @param array<string, int> $teacherTopics
     * @param array<string> $matchedTopics
     * @return float|null
     */
    public function calculate(array $teacherTopics, array $matchedTopics): ?float;

    /**
     * Checks whether the strategy is applicable to the given set of topics.
     *
     * @param array<string> $matchedTopics
     * @return bool
     */
    public function supports(array $matchedTopics): bool;
}