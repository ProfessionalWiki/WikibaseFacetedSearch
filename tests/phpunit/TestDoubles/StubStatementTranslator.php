<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use ProfessionalWiki\WikibaseFacetedSearch\Application\StatementTranslator;
use Wikibase\DataModel\Statement\Statement;

class StubStatementTranslator extends StatementTranslator {

	private mixed $returnValue;

	public function __construct( mixed $returnValue = 'translated value' ) {
		$this->returnValue = $returnValue;
	}

	public function statementToSearchData( Statement $statement ): mixed {
		return $this->returnValue;
	}

} 