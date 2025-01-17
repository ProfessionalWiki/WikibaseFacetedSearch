<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

class Query {

	/**
	 * @param ItemId[] $itemTypes
	 */
	public function __construct(
		public readonly PropertyConstraintsList $constraints,
		private readonly string $freeText = '',
		private readonly array $itemTypes = []
	) {
	}

	public function getConstraintsForProperty( PropertyId $propertyId ): ?PropertyConstraints {
		return $this->constraints->getConstraintsForProperty( $propertyId );
	}

	/**
	 * @return array<string, PropertyConstraints> Constraints indexed by property ID
	 */
	public function getConstraintsPerProperty(): array {
		return $this->constraints->getConstraintsPerProperty();
	}

	public function getFreeText(): string {
		return $this->freeText;
	}

	public function getInstanceItemTypes(): array {
		return $this->itemTypes;
	}

}
