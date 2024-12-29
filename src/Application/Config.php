<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use RuntimeException;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

class Config {

	public function __construct(
		public readonly ?string $linkTargetSitelinkSiteId = null,
		private readonly ?PropertyId $instanceOfId = null,
		private readonly ?FacetConfigList $facets = null
	) {
	}

	public function combine( Config $config ): self {
		return new Config(
			$config->linkTargetSitelinkSiteId ?? $this->linkTargetSitelinkSiteId,
			$config->instanceOfId ?? $this->instanceOfId,
			$config->facets ?? $this->facets
		);
	}

	public function getInstanceOfId(): PropertyId {
		if ( $this->instanceOfId === null ) {
			throw new RuntimeException( 'No instance of ID configured' );
		}

		return $this->instanceOfId;
	}

	public function getFacets(): FacetConfigList {
		return $this->facets ?? new FacetConfigList();
	}

	/**
	 * @return FacetConfig[]
	 */
	public function getFacetConfigForInstanceType( ItemId $instanceTypeId ): array {
		return $this->getFacets()->getFacetConfigForInstanceType( $instanceTypeId )->asArray();
	}

}
