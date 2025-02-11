<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\PropertyId;

interface ValueCounter {

	public function countValues( PropertyId $property ): ValueCounts;

}
