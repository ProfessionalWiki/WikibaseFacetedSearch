<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use RuntimeException;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

class Config {

	public function __construct(
		public readonly ?string $sitelinkSiteId = null,
		private readonly ?PropertyId $itemTypeProperty = null,
		private readonly ?FacetConfigList $facets = null,
		private readonly ?array $icons = null,
	) {
	}

	public function combine( Config $config ): self {
		return new Config(
			$config->sitelinkSiteId ?? $this->sitelinkSiteId,
			$config->itemTypeProperty ?? $this->itemTypeProperty,
			$config->facets ?? $this->facets,
			$config->icons ?? $this->icons,
		);
	}

	public function getItemTypeProperty(): PropertyId {
		if ( $this->itemTypeProperty === null ) {
			throw new RuntimeException( 'No Item Type Property configured' );
		}

		return $this->itemTypeProperty;
	}

	public function getFacets(): FacetConfigList {
		return $this->facets ?? new FacetConfigList();
	}

	public function getIconForItemType( ItemId $itemType ): ?string {
		return $this->icons[$itemType->getSerialization()] ?? null;
	}

	/**
	 * @return FacetConfig[]
	 */
	public function getFacetConfigForItemType( ItemId $itemType ): array {
		return $this->getFacets()->getFacetConfigForItemType( $itemType )->asArray();
	}

	public function getConfigForProperty( ItemId $itemType, PropertyId $propertyId ): ?FacetConfig {
		return $this->getFacets()->getFacetConfigForItemType( $itemType )->getConfigForProperty( $propertyId );
	}

	/**
	 * @return ItemId[]
	 */
	public function getItemTypes(): array {
		$itemTypes = [];

		// TODO: needing this logic is really silly. We have the info as array keys in the JSON.
		// https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/107
		foreach ( $this->getFacets()->asArray() as $facetConfig ) {
			$itemTypes[$facetConfig->itemType->getSerialization()] = $facetConfig->itemType;
		}

		return array_values( $itemTypes );
	}

	public function isComplete(): bool {
		return $this->itemTypeProperty !== null;
	}

}
