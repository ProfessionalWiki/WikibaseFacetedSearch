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
		private readonly InstanceTypeExtractor $instanceTypeExtractor,
		private readonly Config $config
	) {
	}

	/**
	 * @return array<string, mixed>
	 */
	public function translateStatements( StatementList $statements ): array {
		$instanceTypeId = $this->instanceTypeExtractor->getInstanceTypeId( $statements );

		if ( $instanceTypeId === null ) {
			return [];
		}

		$propertyIds = $this->getInstanceTypePropertyIds( $instanceTypeId );

		$values = [];

		foreach ( $propertyIds as $propertyId ) {
			$values[$this->getFieldName( $propertyId )] = $this->translatePropertyStatements( $statements, $propertyId );
		}

		return $values;
	}

	/**
	 * @return PropertyId[]
	 */
	private function getInstanceTypePropertyIds( ItemId $instanceTypeId ): array {
		return array_values(
			array_map(
				fn( FacetConfig $config ) => $config->propertyId,
				$this->config->getFacetConfigForInstanceType( $instanceTypeId )
			)
		);
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
