<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Domain\Facet;

use DataValues\DataValue;

class DataValueFacetValue implements FacetValue {

	public function __construct(
		private readonly string $label,
		private readonly DataValue $value,
		private readonly int $count,
		private readonly bool $isSelected
	) {
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function getValue(): mixed {
		return $this->value;
	}

	public function getCount(): int {
		return $this->count;
	}

	public function isSelected(): bool {
		return $this->isSelected;
	}

	public function getSearchQuery(): string {
		// TODO: format value
		return $this->value->getValue();
	}

}
