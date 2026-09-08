<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCount;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounter;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounts;

class SpyValueCounter implements ValueCounter {

	/**
	 * @var ValueCount[]
	 */
	private readonly array $valueCounts;

	/**
	 * @var PropertyConstraints[]
	 */
	private array $requestedConstraints = [];

	public function __construct( ValueCount ...$valueCounts ) {
		$this->valueCounts = $valueCounts;
	}

	public function countValues( PropertyConstraints $constraints ): ValueCounts {
		$this->requestedConstraints[] = $constraints;
		return new ValueCounts( $this->valueCounts );
	}

	/**
	 * @return PropertyConstraints[]
	 */
	public function getRequestedConstraints(): array {
		return $this->requestedConstraints;
	}

	/**
	 * @return string[]
	 */
	public function getRequestedPropertyIds(): array {
		return array_map(
			fn( PropertyConstraints $constraints ) => $constraints->propertyId->getSerialization(),
			$this->requestedConstraints
		);
	}

}
