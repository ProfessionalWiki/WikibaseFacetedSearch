<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;

class StatementTranslator {

	public function __construct(
		private readonly DataValueTranslator $dataValueTranslator
	) {
	}

	public function statementToSearchData( Statement $statement ): mixed {
		$mainSnak = $statement->getMainSnak();

		if ( !( $mainSnak instanceof PropertyValueSnak ) ) {
			return null;
		}

		return $this->dataValueTranslator->translate( $mainSnak->getDataValue() );
	}

}
