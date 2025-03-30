<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

interface ValueCounter {

	public function countValues( PropertyConstraints $constraints ): ValueCounts;

}
