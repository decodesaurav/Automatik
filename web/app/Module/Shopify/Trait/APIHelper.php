<?php
namespace App\Module\Shopify\Trait;

use Shopify\Clients\Graphql;
use Shopify\Exception\HttpRequestException;
use Shopify\Exception\MissingArgumentException;

trait APIHelper
{
	public function getNextPageInfo(array $link): mixed
	{
		if (count($link)) {
			$links = explode(',', $link[0]);
			$next_page = false;
			foreach ($links as $link) {
				$next_page = false;
				if (strpos($link, 'rel="next"')) {
					$next_page = $link;
				}
			}
			if ($next_page) {
				preg_match('~<(.*?)>~', $next_page, $next);
				$url_components = parse_url($next[1]);
				parse_str($url_components['query'], $params);
				return $params['page_info'];
			}
		}
		return false;
	}

	public function queryOrException($session, $query): array|string|null
	{
		$client = new Graphql($session->shop, $session->access_token);
		$response = $client->query($query);
		$responseBody = $response->getDecodedBody();

		if (!empty($responseBody["errors"])) {
			throw new \Exception("Error while billing the store", (array) $responseBody["errors"]);
		}
		return $responseBody;
	}
}