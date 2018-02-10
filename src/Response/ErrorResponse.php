<?php

declare(strict_types=1);

namespace Api\Application\Response;

class ErrorResponse implements ResponseInterface
{
    private $responseData;

    public function __construct(string $message)
    {
        $this->responseData = ['errors' => ['message' => $message]];
    }

    public function respond(): array
    {
        return $this->responseData;
    }
}
