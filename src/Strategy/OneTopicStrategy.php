<?php declare(strict_types=1);

namespace App\Strategy;

class OneTopicStrategy implements QuoteCalculationStrategyInterface
{
    private const FIRST_POSITION_MULTIPLIER = 0.2;
    private const SECOND_POSITION_MULTIPLIER = 0.25;
    private const THIRD_POSITION_MULTIPLIER = 0.3;

    /**
     * Calculates a quote based on the matched topic and its ranking position in the teacher's top topics.
     *
     * @param array $teacherTopics
     * @param array $matchedTopics
     * @return float|null
     */
    public function calculate(array $teacherTopics, array $matchedTopics): ?float
    {
        if (count($matchedTopics) === 1) {
            $topTopics = $this->getTopTopics($teacherTopics);
            $topic = $matchedTopics[0];
            $position = array_search($topic, array_keys($topTopics), true);
            return match ($position) {
                0 => $teacherTopics[$topic] * self::FIRST_POSITION_MULTIPLIER,
                1 => $teacherTopics[$topic] * self::SECOND_POSITION_MULTIPLIER,
                2 => $teacherTopics[$topic] * self::THIRD_POSITION_MULTIPLIER,
                default => null
            };
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
        return count($matchedTopics) === 1;
    }

    /**
     * Retrieves the top 3 topics ranked by their scores in descending order.
     *
     * @param array $teacherTopics
     * @return array
     */
    private function getTopTopics(array $teacherTopics): array
    {
        arsort($teacherTopics);
        return array_slice($teacherTopics, 0, 3, true);
    }
}
