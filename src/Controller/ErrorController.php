<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ErrorController
{
    public function renderErrorAction(\Throwable $exception, ?DebugLoggerInterface $logger): Response
    {
        return match (true) {
            $exception instanceof HttpException => $this->renderHttpException($exception),
            default => $this->renderGenericException('')
        };
    }

    protected function renderHttpException(HttpException $e): Response
    {
        $httpCode = $e->getStatusCode();

        return match ($httpCode) {
            Response::HTTP_BAD_REQUEST => $this->renderGenericException('Invalid request data', $httpCode),
            Response::HTTP_UNAUTHORIZED => $this->renderGenericException('Unauthorized', httpCode: $httpCode),
            Response::HTTP_INTERNAL_SERVER_ERROR => $this->renderGenericException($e->getMessage(), $e->getCode(), 'TODO', $httpCode),
            default => $this->renderGenericException('')
        };
    }

    protected function renderGenericException(
        string $message,
        ?int $code = null,
        ?string $requestId = null,
        int $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR
    ): Response {
        $data = [
            'message' => $message,
            'code' => $code,
            'request_id' => $requestId,
        ];

        return new JsonResponse(array_filter($data), $httpCode);
    }
}
