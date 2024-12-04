<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Domain\Facet;

class FacetList {

	/**
	 * @var Facet[]
	 */
	private array $facets;

	public function __construct( Facet ...$facets ) {
		$this->facets = $facets;
	}

	/**
	 * @return Facet[]
	 */
	public function asArray(): array {
		return $this->facets;
	}

}
