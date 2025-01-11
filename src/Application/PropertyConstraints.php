<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\PropertyId;

class PropertyConstraints {

	private bool $hasAnyValue = false;
	private bool $hasNoValue = false;

	/**
	 * @param string[] $andSelectedValues Values that all need to match
	 * @param string[] $orSelectedValues Values where any need to match
	 */
	public function __construct(
		public readonly PropertyId $propertyId,
		private readonly array $andSelectedValues = [],
		private array $orSelectedValues = [],
		private ?float $min = null,
		private ?float $max = null,
	) {
	}

	public function requireAnyValue(): self {
		$new = clone $this;
		$new->hasAnyValue = true;
		return $new;
	}

	public function requireNoValue(): self {
		$new = clone $this;
		$new->hasNoValue = true;
		return $new;
	}

	public function hasAnyValue(): bool {
		return $this->hasAnyValue;
	}

	public function hasNoValue(): bool {
		return $this->hasNoValue;
	}

	/**
	 * TODO: is this always string?
	 * @return string[]
	 */
	public function getAndSelectedValues(): array {
		return $this->andSelectedValues;
	}

	/**
	 * TODO: is this always string?
	 * @return string[]
	 */
	public function getOrSelectedValues(): array {
		return $this->orSelectedValues;
	}

	public function withAdditionalAndValue( string $value ): self {
		if ( in_array( $value, $this->andSelectedValues ) ) {
			return $this;
		}

		return new self(
			$this->propertyId,
			[ ...$this->andSelectedValues, $value ],
			$this->orSelectedValues,
			$this->min,
			$this->max
		);
	}

	public function withOrValues( string ...$values ): self {
		$new = clone $this;
		$new->orSelectedValues = array_values( array_unique( $values ) );
		return $new;
	}

	public function getInclusiveMinimum(): ?float {
		return $this->min;
	}

	public function getInclusiveMaximum(): ?float {
		return $this->max;
	}

	public function withInclusiveMinimum( float $min ): self {
		$new = clone $this;
		$new->min = $min;
		return $new;
	}

	public function withInclusiveMaximum( float $max ): self {
		$new = clone $this;
		$new->max = $max;
		return $new;
	}

}
