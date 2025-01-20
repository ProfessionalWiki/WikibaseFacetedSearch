<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraintsList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;

class StubQueryStringParser extends QueryStringParser {

	public function __construct(
		private readonly Query $query = new Query( new PropertyConstraintsList() )
	) {
	}

	public function parse( string $queryString ): Query {
		return $this->query;
	}

}
