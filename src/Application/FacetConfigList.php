<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\ItemId;

class FacetConfigList {

	/**
	 * @var array<string, FacetConfig[]>
	 */
	private array $facetsPerInstanceType = [];

	public function __construct( FacetConfig ...$facets ) {
		foreach ( $facets as $facet ) {
			$this->addFacetConfig( $facet );
		}
	}

	private function addFacetConfig( FacetConfig $facetConfig ): void {
		$this->facetsPerInstanceType[$facetConfig->instanceTypeId->getSerialization()][] = $facetConfig;
	}

	/**
	 * @return FacetConfig[]
	 */
	public function getFacetConfigForInstanceType( ItemId $itemId ): array {
		return $this->facetsPerInstanceType[$itemId->getSerialization()] ?? [];
	}

}
