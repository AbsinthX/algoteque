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
#[Route('/quotes', name: 'quotes_')]
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
     * Handles a POST request to the `/calculate` endpoint to generate quotes based on teacher's input data.
     *
     * Process:
     * - Validates the `Content-Type` header (`application/json`). Returns `415 Unsupported Media Type` if invalid.
     * - Decodes JSON from the request body. Returns `400 Bad Request` for malformed or empty JSON.
     * - Validates the request structure using `TeacherTopicRequest` and Symfony's validator.
     * - If validation fails, returns `422 Unprocessable Entity` with error details.
     * - Extracts top topics and finds matching providers.
     * - Calculates quotes based on the matched providers and topics.
     *
     * @Route("/quote", name="get_quote", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse Returns a JSON response with the following structure:
     * - `toptopics`: List of validated top topics.
     * - `matches`: List of matched providers.
     * - `quotes`: Generated quotes.
     * - In case of an error, the response includes an `error` key with details.
     *
     * Error handling:
     * - `415 Unsupported Media Type` – Invalid `Content-Type` (must be `application/json`).
     * - `400 Bad Request` – Invalid JSON format.
     * - `422 Unprocessable Entity` – Validation errors.
     * - `500 Internal Server Error` – Unexpected system errors.
     *
     * @throws \JsonException If JSON decoding fails.
     * @throws \InvalidArgumentException For invalid argument processing.
     * @throws \Throwable For unexpected errors.
     */
    #[Route('/v1/calculate', name: 'calculate', methods: ['POST'])]
    public function getQuote(Request $request): JsonResponse
    {
        try {
            if ($request->headers->get('Content-Type') !== 'application/json') {
                return new JsonResponse([
                    'error' => 'Invalid Content-Type. Expected application/json.',
                    'code' => 'ERR_INVALID_CONTENT_TYPE'
                ], 415);
            }

            $data = json_decode($request->getContent(), true);

            if ($data === null) {
                return new JsonResponse([
                    'error' => 'Invalid JSON payload.',
                    'code' => 'ERR_INVALID_JSON',
                    'details' => 'Malformed JSON or empty request body.'
                ], 400);
            }
            if (!is_array($data['topics'] ?? null)) {
                return new JsonResponse([
                    'error' => 'Invalid "topics" format. Expected an array.',
                    'code' => 'ERR_INVALID_TOPICS_FORMAT'
                ], 422);
            }

            $teacherRequest = new TeacherTopicRequest($data['topics']);
            $errors = $this->validator->validate($teacherRequest);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
                }
                return new JsonResponse([
                    'error' => 'Validation failed.',
                    'code' => 'ERR_VALIDATION_FAILED',
                    'details' => $errorMessages
                ], 422);
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
            return new JsonResponse([
                'error' => 'Invalid JSON format.',
                'code' => 'ERR_JSON_EXCEPTION'
            ], 400);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'code' => 'ERR_INVALID_ARGUMENT'
            ], 422);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'An unexpected error occurred.',
                'details' => $e->getMessage(),
                'code' => 'ERR_INTERNAL_SERVER_ERROR'
            ], 500);
        }
    }
}
