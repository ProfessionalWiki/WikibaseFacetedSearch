<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use CirrusSearch\Connection;
use CirrusSearch\SearchConfig;
use Elastica\Client;
use Elastica\Response;

class ElasticQueryRunner {

	public function __construct(
		private readonly SearchConfig $config
	) {
	}

	public function runQuery( array $query ): Response {
		return $this->getClient()->request( '_search', 'GET', $query );
	}

	private function getClient(): Client {
		return $this->getConnection()->getClient();
	}

	private function getConnection(): Connection {
		return new Connection( $this->config );
	}

}
