<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Entity\PropertyId;

class ConfigDeserializer {

	public function __construct(
		private readonly ConfigJsonValidator $validator
	) {
	}

	public function deserialize( string $configJson ): Config {
		if ( $this->validator->validate( $configJson ) ) {
			$configArray = json_decode( $configJson, true );

			if ( is_array( $configArray ) ) {
				return $this->newConfig( $configArray );
			}
		}

		return new Config();
	}

	/**
	 * @param array<string, mixed> $configArray
	 */
	private function newConfig( array $configArray ): Config {
		return new Config(
			linkTargetSitelinkSiteId: $configArray['linkTargetSitelinkSiteId'] ?? null,
			instanceOfId: $this->newPropertyId( $configArray['instanceOfId'] ?? null ),
			facets: $this->newFacetConfigList( $configArray['instanceOfValues'] ?? [] )
		);
	}

	private function newPropertyId( ?string $propertyId ): ?PropertyId {
		if ( $propertyId === null ) {
			return null;
		}

		return new NumericPropertyId( $propertyId );
	}

	/**
	 * TODO: also return $itemTypeConfig['label']
	 * @param array<string, array> $configPerItemType
	 */
	private function newFacetConfigList( array $configPerItemType ): ?FacetConfigList {
		if ( $configPerItemType === [] ) {
			return null;
		}

		$facetConfigs = [];

		foreach ( $configPerItemType as $itemId => $itemTypeConfig ) {
			foreach ( $itemTypeConfig['facets'] as $facet ) {
				$facetConfigs[] = $this->newFacetConfig( $itemId, $facet );
			}
		}

		return new FacetConfigList( ...$facetConfigs );
	}

	/**
	 * @param array<string, string> $facetConfig
	 */
	private function newFacetConfig( string $itemId, array $facetConfig ): FacetConfig {
		return new FacetConfig(
			instanceTypeId: new ItemId( $itemId ),
			propertyId: new NumericPropertyId( $facetConfig['property'] ),
			type: FacetType::from( $facetConfig['type'] )
		);
	}

}
