<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\TeacherTopicRequest;
use App\Service\ProviderMatchingService;
use App\Service\QuoteCalculationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller responsible for generating quotes based on input data.
 */
readonly class QuoteController
{
    /**
     * Constructor to initialize required services.
     *
     * @param ValidatorInterface      $validator Symfony service for validating input data.
     * @param ProviderMatchingService $providerMatchingService Service for matching providers based on topics.
     * @param QuoteCalculationService $quoteCalculationService Service used for calculating quotes.
     */
    public function __construct(private ValidatorInterface $validator,
                                private ProviderMatchingService $providerMatchingService,
                                private QuoteCalculationService $quoteCalculationService)
    {
    }

    /**
     * Handles a POST request to the `/quote` endpoint to generate quotes based on teacher's input data.
     *
     * Process:
     * - Validates that the request contains the correct `Content-Type` header (`application/json`).
     * - Decodes JSON data from the request body.
     * - Validates the data structure using the `TeacherTopicRequest` object.
     * - Matches providers based on teacher topics.
     * - Calculates quotes based on matches and input data.
     *
     * @Route("/quote", name="get_quote", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse Returns a JSON response with the following data:
     * - `toptopics`: List of top topics provided.
     * - `matches`: Matched providers for the topics.
     * - `quotes`: Generated quotes based on the matches.
     * - In case of an error, returns an `error` key with the appropriate message.
     *
     * @throws \JsonException
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    #[Route('/quote', name: 'get_quote', methods: ['POST'])]
    public function getQuote(Request $request): JsonResponse
    {
        try {
            if (!$request->headers->contains('Content-Type', 'application/json')) {
                return new JsonResponse(['error' => 'Invalid Content-Type. Expected application/json.'], 400);
            }

            $data = json_decode($request->getContent(), true);

            if ($data === null) {
                return new JsonResponse(['error' => 'Invalid JSON payload.'], 400);
            }
            if (!is_array($data['topics'] ?? null)) {
                return new JsonResponse(['error' => 'Invalid "topics" format. Expected an array.'], 400);
            }

            $teacherRequest = new TeacherTopicRequest($data['topics']);
            $errors = $this->validator->validate($teacherRequest);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
                }
                return new JsonResponse(['error' => $errorMessages], 400);
            }

            $toptopics = $teacherRequest->getTopTopics();
            $matches = $this->providerMatchingService->matchTopics($toptopics);
            $quotes = $this->quoteCalculationService->calculateQuotes($teacherRequest->getTopTopics(), $matches);

            return new JsonResponse([
                'toptopics' => $toptopics,
                'matches' => $matches,
                'quotes' => $quotes,
            ]);

        } catch (\JsonException $e) {
            return new JsonResponse(['error' => 'Invalid JSON format.'], 400);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'An unexpected error occurred.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
