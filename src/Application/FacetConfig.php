<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

class FacetConfig {

	/**
	 * @param array<string, mixed> $typeSpecificConfig
	 */
	public function __construct(
		public readonly ItemId $itemType,
		public readonly PropertyId $propertyId,
		public readonly FacetType $type,
		public readonly array $typeSpecificConfig = [],
	) {
	}

}
