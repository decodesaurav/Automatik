<?php

namespace App\Module\Shopify\Helper;

use App\Exceptions\ShopifyApiRateLimitException;
use Psr\Http\Client\ClientExceptionInterface;
use Shopify\Clients\HttpResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ShopifyHelper
{
	public static function handleApiException($callback)
	{
		try {
			$response = $callback();
			if (
				$response instanceof HttpResponse &&
				$response->getStatusCode() === ResponseAlias::HTTP_TOO_MANY_REQUESTS
			) {
				throw new ShopifyApiRateLimitException('Rate Limit Exceeded', 10, $response);
			}
			return $response;
		} catch (ClientExceptionInterface $e) {
			logger($e);
			throw $e;
		}
	}
	public static function decodeGraphqlID($gid)
	{
		return array_reverse(explode('/', $gid))[0];
	}
}