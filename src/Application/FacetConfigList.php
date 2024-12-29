<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\ItemId;

class FacetConfigList {

	/**
	 * @var FacetConfig[]
	 */
	private array $facets;

	public function __construct( FacetConfig ...$facetConfigs ) {
		$this->facets = $facetConfigs;
	}

	public function getFacetConfigForInstanceType( ItemId $itemId ): self {
		return new self(
			...array_filter(
				$this->facets,
				fn( FacetConfig $facetConfig ) => $facetConfig->instanceTypeId->equals( $itemId )
			)
		);
	}

	/**
	 * @return FacetConfig[]
	 */
	public function asArray(): array {
		return $this->facets;
	}

}
