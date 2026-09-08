<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

/**
 * Counts nothing, for when there is no search engine query to aggregate over.
 */
class NoOpValueCounter implements ValueCounter {

	public function countValues( PropertyConstraints $constraints ): ValueCounts {
		return new ValueCounts( [] );
	}

}
