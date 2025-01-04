<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Statement\StatementList;
use WikiPage;

interface StatementsLookup {

	public function getStatements( WikiPage $page ): StatementList;

}
