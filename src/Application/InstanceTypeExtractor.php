<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\StatementList;

class InstanceTypeExtractor {

	public function __construct(
		private readonly PropertyId $instanceType
	) {
	}

	public function getInstanceTypeId( StatementList $statements ): ?ItemId {
		$instanceTypeStatements = $statements->getByPropertyId( $this->instanceType )->getBestStatements();

		if ( $instanceTypeStatements->isEmpty() ) {
			return null;
		}

		$firstMainSnak = $this->getFirstMainSnak( $instanceTypeStatements );

		if ( $firstMainSnak === null ) {
			return null;
		}

		$dataValue = $firstMainSnak->getDataValue();

		if ( !( $dataValue instanceof EntityIdValue ) ) {
			return null;
		}

		$entityId = $dataValue->getEntityId();

		if ( !( $entityId instanceof ItemId ) ) {
			return null;
		}

		return $entityId;
	}

	private function getFirstMainSnak( StatementList $statements ): ?PropertyValueSnak {
		foreach ( $statements->toArray() as $statement ) {
			$mainSnak = $statement->getMainSnak();

			if ( $mainSnak instanceof PropertyValueSnak ) {
				return $mainSnak;
			}
		}

		return null;
	}

}
