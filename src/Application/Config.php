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
	public function getFacetConfigForItemType( ItemId $itemTypeId ): array {
		return $this->getFacets()->getFacetConfigForItemType( $itemTypeId )->asArray();
	}

	public function getConfigForProperty( ItemId $itemTypeId, PropertyId $propertyId ): ?FacetConfig {
		return $this->getFacets()->getFacetConfigForItemType( $itemTypeId )->getConfigForProperty( $propertyId );
	}

	/**
	 * @return ItemId[]
	 */
	public function getItemTypes(): array {
		$itemTypes = [];

		// TODO: needing this logic is really silly. We have the info as array keys in the JSON.
		// https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/107
		foreach ( $this->getFacets()->asArray() as $facetConfig ) {
			$itemTypes[$facetConfig->itemTypeId->getSerialization()] = $facetConfig->itemTypeId;
		}

		return array_values( $itemTypes );
	}

}
