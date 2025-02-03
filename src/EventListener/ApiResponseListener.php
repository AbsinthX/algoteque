<?php declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ApiResponseListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * This listener logs error responses for a specific API route (`/api/quotes/v1/calculate`).
     * It captures request details and logs them only when the HTTP status code is 400 or higher,
     * helping to debug issues with this particular endpoint.
     *
     * @param ResponseEvent $event
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        if ($request->getRequestUri() === '/api/quotes/v1/calculate' && $response->getStatusCode() >= 400) {
            $clientBody = $request->getContent();
            $requestHeaders = $request->headers->all();
            $getParams = $request->query->all();
            $postParams = $request->request->all();

            $this->logger->error('API Error Response', [
                'status_code' => $response->getStatusCode(),
                'response' => json_decode($response->getContent(), true),
                'uri' => $request->getRequestUri(),
                'method' => $request->getMethod(),
                'client_headers' => $requestHeaders,
                'query_params' => $getParams,
                'post_params' => $postParams,
                'body' => $clientBody
            ]);
        }
    }
}
