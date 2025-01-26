<?php declare(strict_types=1);

namespace App\Strategy;

class TwoTopicsStrategy implements QuoteCalculationStrategyInterface
{
    private const ONE_POSITION_MULTIPLIER = 0.1;

    /**
     * Calculates a quote based on the matched topic and its ranking position in the teacher's top topics.
     *
     * @param array $teacherTopics
     * @param array $matchedTopics
     * @return float|null
     */
    public function calculate(array $teacherTopics, array $matchedTopics): ?float
    {
        if (count($matchedTopics) === 2) {
            return array_sum(array_map(fn($topic) => $teacherTopics[$topic], $matchedTopics)) * self::ONE_POSITION_MULTIPLIER;
        }
        return null;
    }

    /**
     * Checks if the current strategy supports given matched topics.
     *
     * @param array $matchedTopics
     * @return bool
     */
    public function supports(array $matchedTopics): bool
    {
        return count($matchedTopics) === 2;
    }
}
