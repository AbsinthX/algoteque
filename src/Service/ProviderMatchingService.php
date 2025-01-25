<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\ProviderRepository;

class ProviderMatchingService
{
    private ProviderRepository $repository;

    public function __construct(ProviderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function matchProviders(array $topics): array
    {
        $providers = $this->repository->getProviders();
        $matches = [];

        foreach ($providers as $provider => $providerTopics) {
            $providerTopicsArray = explode('+', $providerTopics);
            $commonTopics = array_intersect(array_keys($topics), $providerTopicsArray);

            if (count($commonTopics) > 0) {
                $matches[$provider] = $commonTopics;
            }
        }

        return $matches;
    }
}