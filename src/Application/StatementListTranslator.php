<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

class StatementListTranslator {

	public function __construct(
		private readonly StatementTranslator $statementTranslator,
		private readonly ItemTypeExtractor $itemTypeExtractor,
		private readonly Config $config
	) {
	}

	/**
	 * Builds a list of values to store in the elastic search index per elastic field name (key).
	 * @return array<string, mixed>
	 */
	public function translateStatements( StatementList $statements ): array {
		$itemType = $this->itemTypeExtractor->getItemType( $statements );

		if ( $itemType === null ) {
			return [];
		}

		$propertyIds = $this->getPropertiesToIndex( $itemType, $statements );

		$values = [];

		foreach ( $propertyIds as $propertyId ) {
			$values[$this->getFieldName( $propertyId )] = $this->translatePropertyStatements( $statements, $propertyId );
		}

		return $values;
	}

	/**
	 * @return PropertyId[]
	 */
	private function getPropertiesToIndex( ItemId $itemType, StatementList $statements ): array {
		if ( $this->config->indexAllProperties ) {
			// TODO: is this sufficient, or do we need to end up explicitly indexing properties not present as empty?
			$properties = $statements->getPropertyIds();
		}
		else {
			$properties = $this->config->getPropertiesWithFacetsForItemType( $itemType );
		}

		$properties[] = $this->config->getItemTypeProperty();

		return $properties;
	}

	private function translatePropertyStatements( StatementList $statements, PropertyId $propertyId ): array {
		// TODO: should empty field (all statements removed) be [] or null?
		// https://github.com/ProfessionalWiki/WikibaseFacetedSearch/pull/51/files#r1901363923

		return array_filter(
			array_map(
				function( Statement $statement ): mixed {
					return $this->statementTranslator->statementToSearchData( $statement );
				},
				$statements->getByPropertyId( $propertyId )->getBestStatements()->toArray()
			),
			fn( mixed $value ): bool => $value !== null
		);
	}

	private function getFieldName( PropertyId $propertyId ): string {
		return 'wbfs_' . $propertyId->getSerialization();
	}

}
