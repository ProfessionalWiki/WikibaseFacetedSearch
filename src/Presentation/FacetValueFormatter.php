<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\LabelLookup;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookupException;

class FacetValueFormatter {

	/** @var array<string, ?string> */
	private array $propertyIdToType = [];

	public function __construct(
		private readonly PropertyDataTypeLookup $dataTypeLookup,
		private readonly LabelLookup $labelLookup
	) {
	}

	public function getLabel( string $value, PropertyId $propertyId ): string {
		$dataTypeId = $this->getPropertyDataTypeId( $propertyId );

		if ( $dataTypeId === null || $dataTypeId !== 'wikibase-item' ) {
			return $value;
		}

		return $this->labelLookup->getLabel( new ItemId( $value ) )?->getText() ?? $value;
	}

	private function getPropertyDataTypeId( PropertyId $propertyId ): ?string {
		$key = $propertyId->getSerialization();

		if ( !array_key_exists( $key, $this->propertyIdToType ) ) {
			$this->propertyIdToType[$key] = $this->getDataTypeIdForProperty( $propertyId );
		}

		return $this->propertyIdToType[$key];
	}

	private function getDataTypeIdForProperty( PropertyId $propertyId ): ?string {
		try {
			return $this->dataTypeLookup->getDataTypeIdForProperty( $propertyId );
		} catch ( PropertyDataTypeLookupException ) {
			return null;
		}
	}
}
