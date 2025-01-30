<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;

class StubPropertyDataTypeLookup implements PropertyDataTypeLookup {

	public function __construct(
		private readonly ?string $dataType
	) {
	}

	public function getDataTypeIdForProperty( PropertyId $propertyId ): ?string {
		return $this->dataType;
	}

}
