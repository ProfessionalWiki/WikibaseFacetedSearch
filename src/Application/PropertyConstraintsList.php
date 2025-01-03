<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\PropertyId;

class PropertyConstraintsList {

	/**
	 * Constraints indexed by property ID
	 * @var array<string, PropertyConstraints>
	 */
	private array $constraintsByPropertyId = [];

	public function __construct( PropertyConstraints ...$constraints ) {
		foreach ( $constraints as $constraint ) {
			$this->constraintsByPropertyId[$constraint->propertyId->getSerialization()] = $constraint;
		}
	}

	public function getConstraintsForProperty( PropertyId $propertyId ): ?PropertyConstraints {
		return $this->constraintsByPropertyId[$propertyId->getSerialization()] ?? null;
	}

	/**
	 * @return array<string, PropertyConstraints> Constraints indexed by property ID
	 */
	public function getConstraintsPerProperty(): array {
		return $this->constraintsByPropertyId;
	}

	public function getOrCreateConstraints( PropertyId $propertyId ): PropertyConstraints {
		return $this->getConstraintsForProperty( $propertyId ) ?? new PropertyConstraints( $propertyId );
	}

	public function withConstraint( PropertyConstraints $constraint ): self {
		$new = new self();
		$new->constraintsByPropertyId = array_merge(
			$this->constraintsByPropertyId,
			[ $constraint->propertyId->getSerialization() => $constraint ]
		);
		return $new;
	}

}
