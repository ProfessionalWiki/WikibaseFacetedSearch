<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

class FacetConfig {

	public function __construct(
		public readonly ItemId $instanceTypeId,
		public readonly PropertyId $propertyId,
		public readonly FacetType $type,
		// TODO: type specific config array
	) {
	}

}
