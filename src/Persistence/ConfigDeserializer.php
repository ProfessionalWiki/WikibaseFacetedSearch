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
			itemTypeProperty: $this->newPropertyId( $configArray['itemTypeProperty'] ?? null ),
			facets: $this->newFacetConfigList( $configArray['configPerItemType'] ?? [] )
		);
	}

	private function newPropertyId( ?string $propertyId ): ?PropertyId {
		if ( $propertyId === null ) {
			return null;
		}

		return new NumericPropertyId( $propertyId );
	}

	/**
	 * @param array<string, array> $configPerItemType
	 */
	private function newFacetConfigList( array $configPerItemType ): ?FacetConfigList {
		if ( $configPerItemType === [] ) {
			return null;
		}

		$facetConfigs = [];

		foreach ( $configPerItemType as $itemId => $itemTypeConfig ) {
			foreach ( $itemTypeConfig['facets'] ?? [] as $propertyId => $facetConfig ) {
				$facetConfigs[] = $this->newFacetConfig( $itemId, $propertyId, $facetConfig );
			}
		}

		return new FacetConfigList( ...$facetConfigs );
	}

	/**
	 * @param array<string, mixed> $facetConfig
	 */
	private function newFacetConfig( string $itemId, string $propertyId, array $facetConfig ): FacetConfig {
		$typeSpecificConfig = $facetConfig;
		unset( $typeSpecificConfig['type'] );

		return new FacetConfig(
			itemType: new ItemId( $itemId ),
			propertyId: new NumericPropertyId( $propertyId ),
			type: FacetType::from( $facetConfig['type'] ),
			typeSpecificConfig: $typeSpecificConfig
		);
	}

}
