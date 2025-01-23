<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search;

use CirrusSearch\CirrusSearch;
use Exception;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use SearchIndexField;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;

class SearchIndexFieldsBuilder {

	public function __construct(
		private readonly CirrusSearch $engine,
		private readonly Config $config,
		private readonly PropertyDataTypeLookup $dataTypeLookup
	) {
	}

	/**
	 * @return array<string, SearchIndexField>
	 */
	public function createFields(): array {
		return $this->makeItemTypeSearchFieldMapping( $this->config->getItemTypeProperty() )
			+ $this->makeFacetSearchFieldMappings( $this->config->getFacets()->asArray() );
	}

	/**
	 * @return array<string, SearchIndexField>
	 */
	private function makeItemTypeSearchFieldMapping( PropertyId $propertyId ): array {
		$name = $this->getPropertyFieldName( $propertyId );

		return [
			$name => $this->makeSearchFieldMapping( $name, SearchIndexField::INDEX_TYPE_KEYWORD )
		];
	}

	private function getFieldTypeForPropertyId( PropertyId $propertyId ): ?string {
		try {
			$dataTypeId = $this->dataTypeLookup->getDataTypeIdForProperty( $propertyId );
		} catch ( Exception ) {
			return null;
		}

		return $this->getFieldTypeForDataTypeId( $dataTypeId );
	}

	private function getFieldTypeForDataTypeId( string $dataTypeId ): ?string {
		return match ( $dataTypeId ) {
			'quantity' => SearchIndexField::INDEX_TYPE_NUMBER,
			'string' => SearchIndexField::INDEX_TYPE_KEYWORD,
			'time' => SearchIndexField::INDEX_TYPE_DATETIME,
			'wikibase-item' => SearchIndexField::INDEX_TYPE_KEYWORD,
			default => null
		};
	}

	private function getPropertyFieldName( PropertyId $propertyId ): string {
		return 'wbfs_' . $propertyId->getSerialization();
	}

	/**
	 * @param FacetConfig[] $facets
	 * @return array<string, SearchIndexField>
	 */
	private function makeFacetSearchFieldMappings( array $facets ): array {
		$fields = [];

		foreach ( $facets as $facetConfig ) {
			$fieldType = $this->getFieldTypeForPropertyId( $facetConfig->propertyId );

			if ( $fieldType === null ) {
				continue;
			}

			$name = $this->getPropertyFieldName( $facetConfig->propertyId );
			$fields[$name] = $this->makeSearchFieldMapping( $name, $fieldType );
		}

		return $fields;
	}

	private function makeSearchFieldMapping( string $name, string $fieldType ): SearchIndexField {
		if ( $fieldType === SearchIndexField::INDEX_TYPE_KEYWORD ) {
			return new AggregatableKeywordIndexField( $name, $fieldType, $this->engine->getConfig() );
		}

		return $this->engine->makeSearchFieldMapping( $name, $fieldType );
	}

}
