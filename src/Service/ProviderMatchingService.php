<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\ProviderRepository;

readonly class ProviderMatchingService
{
    public function __construct(private ProviderRepository $repository)
    {
    }

    /**
     * Match providers topics
     *
     * @param array $teacherTopics
     * @return array
     */
    public function matchTopics(array $teacherTopics): array
    {
        $matches = [];
        foreach ($this->repository->getProviders() as $provider => $topics) {
            $providerTopics = explode('+', $topics);
            $matchingTopics = array_intersect(array_keys($teacherTopics), $providerTopics);
            if (!empty($matchingTopics)) {
                $matches[$provider] = array_values($matchingTopics);
            }
        }
        return $matches;
    }
}