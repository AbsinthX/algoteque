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

    #[Assert\NotBlank]
    #[Assert\Type("array")]
    #[Assert\All([
        new Assert\Type(type: "integer"),
        new Assert\Positive(),
    ])]
    private array $topics;

    /**
     * @param array<string, int> $topics
     */
    public function __construct(array $topics)
    {
        $this->topics = $topics;
    }

    /**
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
        foreach (array_keys($object->topics) as $key) {
            if (!TopicEnum::isValid($key)) {
                $context->buildViolation(sprintf('The topic "%s" is not valid.', $key))
                    ->atPath("topics[$key]")
                    ->addViolation();
            }
        }
    }
}