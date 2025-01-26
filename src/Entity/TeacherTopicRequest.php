<?php declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\TopicEnum;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\Callback([self::class, 'validateKeys'])]
class TeacherTopicRequest
{
    /**
     * Default top topics to return.
     *
     * @var int
     */
    private const DEFAULT_TOP_COUNT = 3;

    /**
     * array<string, int>
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type("array")]
        #[Assert\All([
            new Assert\Type(type: "integer"),
            new Assert\Positive(),
        ])]
        private array $topics
    ) {
    }

    /**
     * Get all topics
     *
     * @return array<string, int>
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    /**
     * Get top topics sorted by their values in descending order.
     *
     * @param int|null $count
     * @return array<string, int>
     */
    public function getTopTopics(?int $count = null): array
    {
        $count = $count ?? self::DEFAULT_TOP_COUNT;
        $sortedTopics = $this->sortTopicsDescending();
        return array_slice($sortedTopics, 0, $count, true);
    }

    /**
     * Sort topics values in descending order.
     *
     * @return array<string, int>
     */
    private function sortTopicsDescending(): array
    {
        arsort($this->topics);
        return $this->topics;
    }

    /**
     * Validates that the keys in the topics array are valid topics from TopicEnum.
     *
     * @param ExecutionContextInterface $context
     */
    public static function validateKeys(self $object, ExecutionContextInterface $context): void
    {
        $requiredKeys = TopicEnum::cases();
        $topicKeys = array_keys($object->topics);

        foreach ($requiredKeys as $requiredKey) {
            if (!in_array($requiredKey->value, $topicKeys, true)) {
                $context->buildViolation(sprintf('The key "%s" is missing from topics.', $requiredKey->value))
                    ->atPath('topics')
                    ->addViolation();
            }
        }

        foreach ($topicKeys as $key) {
            if (!TopicEnum::isValid($key)) {
                $context->buildViolation(sprintf('The topic "%s" is not valid.', $key))
                    ->atPath("topics[$key]")
                    ->addViolation();
            }
        }
    }
}
