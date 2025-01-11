<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\PropertyId;

/**
 * TODO:
 * Turn into interface and created implementations
 * See https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/23
 */
class ValueCounter {

	/**
	 * TODO: needs more info than the property ID: also the rest of the current query
	 */
	public function countValues( PropertyId $property ): ValueCounts {
		return new ValueCounts( [
			new ValueCount( 'Bob', 5 ), // TODO
			new ValueCount( 'Alice', 3 ),
			new ValueCount( 'Charlie', 1 ),
		] );
	}

}
