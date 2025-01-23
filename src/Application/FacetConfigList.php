<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

class FacetConfigList {

	/**
	 * @var FacetConfig[]
	 */
	private array $facets;

	public function __construct( FacetConfig ...$facetConfigs ) {
		$this->facets = $facetConfigs;
	}

	public function getFacetConfigForItemType( ItemId $itemId ): self {
		return new self(
			...array_filter(
				$this->facets,
				fn( FacetConfig $facetConfig ) => $facetConfig->itemType->equals( $itemId )
			)
		);
	}

	/**
	 * @return FacetConfig[]
	 */
	public function asArray(): array {
		return $this->facets;
	}

	public function getConfigForProperty( PropertyId $propertyId ): ?FacetConfig {
		foreach ( $this->facets as $facetConfig ) {
			if ( $facetConfig->propertyId->equals( $propertyId ) ) {
				return $facetConfig;
			}
		}

		return null;
	}

}
