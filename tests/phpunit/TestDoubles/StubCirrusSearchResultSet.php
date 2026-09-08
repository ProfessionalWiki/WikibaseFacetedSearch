<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use CirrusSearch\Search\EmptySearchResultSet;
use Elastica\Query as ElasticaQuery;
use Elastica\Query\AbstractQuery;
use Elastica\Response;
use Elastica\ResultSet;

/**
 * A hitless CirrusSearch result set that, unlike EmptySearchResultSet, carries an
 * Elasticsearch query.
 */
class StubCirrusSearchResultSet extends EmptySearchResultSet {

	private readonly ResultSet $elasticaResultSet;

	public function __construct( AbstractQuery $query ) {
		parent::__construct( false );

		$this->elasticaResultSet = new ResultSet(
			new Response( [] ),
			( new ElasticaQuery() )->setQuery( $query ),
			[]
		);
	}

	public function getElasticaResultSet(): ResultSet {
		return $this->elasticaResultSet;
	}

}
