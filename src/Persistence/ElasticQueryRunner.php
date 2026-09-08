<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use CirrusSearch\Connection;
use CirrusSearch\SearchConfig;
use Elastica\Index;
use Elastica\Response;

class ElasticQueryRunner {

	public function __construct(
		private readonly SearchConfig $config
	) {
	}

	public function runQuery( array $query ): Response {
		return $this->getIndex()->request( '_search', 'GET', $query, [ 'ignore_unavailable' => 'true' ] );
	}

	/**
	 * The all-types index of this wiki, so queries do not reach indexes of other wikis on the same cluster.
	 */
	private function getIndex(): Index {
		return $this->getConnection()->getIndex( $this->config->get( SearchConfig::INDEX_BASE_NAME ) );
	}

	private function getConnection(): Connection {
		return Connection::getPool( $this->config );
	}

}
