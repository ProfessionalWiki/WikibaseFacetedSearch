<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Domain\Facet;

class FacetValueList {

	/**
	 * @var FacetValue[]
	 */
	private array $values;

	public function __construct( FacetValue ...$facetValues ) {
		$this->values = $facetValues;
	}

	/**
	 * @return FacetValue[]
	 */
	public function asArray(): array {
		return $this->values;
	}

	public function getSelected(): FacetValueList {
		return new self(
			...array_filter(
				$this->values,
				fn( FacetValue $value ) => $value->isSelected()
			)
		);
	}

}
