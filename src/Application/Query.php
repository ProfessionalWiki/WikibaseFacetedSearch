<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\PropertyId;

final class Query {

	public function __construct(
		public readonly PropertyConstraintsList $constraints,
		private readonly string $freeText = ''
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

}
