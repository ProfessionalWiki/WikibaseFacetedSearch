<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\ItemId;

class FacetConfigList {

	/**
	 * @var array<string, FacetConfig[]>
	 */
	private array $facetsPerItemId = [];

	public function __construct( FacetConfig ...$facets ) {
		foreach ( $facets as $facet ) {
			$this->addFacetConfig( $facet );
		}
	}

	private function addFacetConfig( FacetConfig $facetConfig ): void {
		$this->facetsPerItemId[$facetConfig->itemId->getSerialization()][] = $facetConfig;
	}

	/**
	 * @return FacetConfig[]
	 */
	public function getFacetConfigForItemId( ItemId $itemId ): array {
		return $this->facetsPerItemId[$itemId->getSerialization()] ?? [];
	}

}
