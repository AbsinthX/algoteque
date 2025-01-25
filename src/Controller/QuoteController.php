<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\TeacherTopicRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\ProviderMatchingService;

class QuoteController
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    #[Route('/validate-request', name: 'validate_request', methods: ['POST'])]
    public function validateRequest(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return new JsonResponse(['error' => 'Invalid JSON payload.'], 400);
        }

        $teacherRequest = new TeacherTopicRequest($data['topics'] ?? []);
        $errors = $this->validator->validate($teacherRequest);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            return new JsonResponse(['error' => $errorMessages], 400);
        }

        return new JsonResponse([
            'message' => 'All good man!',
            'top_topics' => $teacherRequest->getTopTopics()
        ]);
    }

    #[Route('/match-providers', name: 'match_providers', methods: ['POST'])]
    public function matchProviders(Request $request, ProviderMatchingService $matchingService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $teacherRequest = new TeacherTopicRequest($data['topics'] ?? []);
        $matches = $matchingService->matchProviders($teacherRequest->getTopTopics());
        foreach ($matches as $provider => $topics) {
            $matches[$provider] = array_values($topics);
        }

        return new JsonResponse(['matches' => $matches]);
    }
}