<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search;

use Exception;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use SearchEngine;
use SearchIndexField;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;

class SearchIndexFieldsBuilder {

	public function __construct(
		private readonly SearchEngine $engine,
		private readonly Config $config,
		private readonly PropertyDataTypeLookup $dataTypeLookup
	) {
	}

	/**
	 * @return array<string, SearchIndexField>
	 */
	public function createFields(): array {
		$fields = [];

		foreach ( $this->config->getFacets()->asArray() as $facetConfig ) {
			$fieldType = $this->getFieldTypeForPropertyId( $facetConfig->propertyId );

			if ( $fieldType === null ) {
				continue;
			}

			$name = $this->getFacetFieldName( $facetConfig );
			$fields[$name] = $this->engine->makeSearchFieldMapping( $name, $fieldType );
		}

		return $fields;
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

	private function getFacetFieldName( FacetConfig $facetConfig ): string {
		return 'wbfs_' . $facetConfig->propertyId->getSerialization();
	}

}
