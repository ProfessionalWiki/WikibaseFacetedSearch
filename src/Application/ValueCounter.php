<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\PropertyId;

interface ValueCounter {

	/**
	 * TODO: needs more info than the property ID: also the rest of the current query
	 */
	public function countValues( PropertyId $property ): ValueCounts;

}
