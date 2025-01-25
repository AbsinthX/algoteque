<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProviderRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{

    public function __construct(private ProviderRepository $repository)
    {
    }

    #[Route('/test-providers', name: 'test_providers')]
    public function testProviders(): JsonResponse
    {
        return new JsonResponse($this->repository->getProviders());
    }
}