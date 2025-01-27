<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeLabelLookup;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookupException;

class FacetLabelBuilder {

	/** @var array<string, ?string> */
	private array $propertyIdToType = [];

	public function __construct(
		private readonly PropertyDataTypeLookup $dataTypeLookup,
		private readonly ItemTypeLabelLookup $labelLookup
	) {
	}

	public function getTabLabel( ItemId $itemType ): string {
		return $this->labelLookup->getLabel( $itemType );
	}

	public function getFacetLabel( PropertyId $propertyId ): string {
		return $this->labelLookup->getLabel( $propertyId );
	}

	public function getItemLabel( string $value, PropertyId $propertyId ): string {
		$dataTypeId = $this->getPropertyDataTypeId( $propertyId );

		if ( $dataTypeId === null || $dataTypeId !== 'wikibase-item' ) {
			return $value;
		}

		return $this->labelLookup->getLabel( new ItemId( $value ) );
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
