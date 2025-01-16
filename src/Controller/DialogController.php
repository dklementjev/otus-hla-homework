<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[Route(name: 'dialog_', path: '/dialog')]
class DialogController extends BaseController
{
    const REQUEST_ID_HEADER = 'x-request-id';

    public function __construct(
        protected readonly HttpClientInterface $httpClient,
        protected readonly string $proxiedBaseUrl,
        SerializerInterface $serializer,
        protected readonly LoggerInterface $logger,
        #[Autowire(param: 'controller.default_json_encode_options')]
        int $jsonEncodeOptions
    ){
        parent::__construct($serializer, $jsonEncodeOptions);
    }

    #[Route(name: 'pm_send', path: '/{other_user_id}/send', methods: ['POST'], requirements: ['other_user_id' => '\d+'])]
    public function sendMessageAction(Request $request): Response
    {
        $otherUserId = $request->attributes->getInt('other_user_id');
        $bearerToken = $this->getBearerToken($request);
        $requestIdHeader = $this->getRequestId($request);

        $response = $this->forwardRequest(
            Request::METHOD_POST,
            $this->getServiceUrl('/dialog/' . $otherUserId . '/send'),
            $bearerToken,
            $requestIdHeader,
            $request->getContent()
        );

        return new Response(
            $response->getContent(false),
            $response->getStatusCode(),
            $this->getProxiedRequestHeaders($request),
        );
    }

    #[Route(name: 'pm_list', path: '/{other_user_id}/list', methods: ['GET'], requirements: ['other_user_id' => '\d+'])]
    public function listDialogMessagesAction(Request $request): Response
    {
        $otherUserId = $request->attributes->getInt('other_user_id');
        $bearerToken = $this->getBearerToken($request);
        $requestIdHeader = $this->getRequestId($request);

        $response = $this->forwardRequest(
            Request::METHOD_GET,
            $this->getServiceUrl('/dialog/' . $otherUserId . '/list'),
            $bearerToken,
            $requestIdHeader
        );

        return new Response(
            $response->getContent(false),
            $response->getStatusCode(),
            $this->getProxiedRequestHeaders($request),
        );
    }

    protected function forwardRequest(
        string $httpMethod,
        string $path,
        ?string $bearerToken,
        ?string $requestId,
        $requestBody = null
    ): ResponseInterface {
        $requestOptions = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization: Bearer ' . $bearerToken,
                'X-Request-Id: ' . $requestId,
            ]
        ];

        if (!empty($requestBody)) {
            $requestOptions['body'] = $requestBody;
        }

        $this->logger->debug(
            'forwardRequest',
            [
                'method' => $httpMethod,
                'path' => $path,
                'options' => $requestOptions
            ]
        );

        return $this->httpClient->request($httpMethod, $path, $requestOptions);
    }

    protected function getRequestId(Request $request): ?string
    {
        return $request->headers->get(self::REQUEST_ID_HEADER) ?? '';
    }

    protected function getBearerToken(Request $request): ?string
    {
        $header = $request->headers->get('authorization');

        if ($header && preg_match('/^bearer[ ]+(.*)$/i', $header, $m)) {
            return $m[1];
        }

        return null;
    }

    protected function getServiceUrl(string $path): string
    {
        return join(
            '/',
            [
                $this->proxiedBaseUrl,
                ltrim($path, '/'),
            ]
        );
    }

    protected function getProxiedRequestHeaders(Request $request): array
    {
        $res = [];

        $res[self::REQUEST_ID_HEADER] = $request->headers->get(self::REQUEST_ID_HEADER);

        return $res;
    }
}
