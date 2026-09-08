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
		return $this->getIndex()->request( '_search', 'GET', $query );
	}

	/**
	 * The alias covering all of this wiki's indexes, so queries do not reach the indexes of other wikis
	 * on the same cluster.
	 */
	private function getIndex(): Index {
		return $this->getConnection()->getIndex( $this->config->get( SearchConfig::INDEX_BASE_NAME ) );
	}

	private function getConnection(): Connection {
		return Connection::getPool( $this->config );
	}

}
