<?php

namespace App\Exceptions;

use Exception;
use Shopify\Clients\HttpResponse;

class ShopifyApiRateLimitException extends Exception
{
    public HttpResponse $response;
    private int $retryAfter;

    public function __construct(string $message, int $retryAfter = 10, ?HttpResponse $response = null)
    {
        $this->retryAfter = $retryAfter;
        $this->response = $response;
        parent::__construct($message);
    }
    public function getRetryAfter(): int
    {
        return $this->retryAfter + rand(10, 30);
    }
}
