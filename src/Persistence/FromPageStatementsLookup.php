<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\StatementsLookup;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\Repo\Content\ItemContent;
use WikiPage;

class FromPageStatementsLookup implements StatementsLookup {

	public function getStatements( WikiPage $page ): StatementList {
		$content = $page->getContent();

		if ( !( $content instanceof ItemContent ) ) {
			return new StatementList();
		}

		return $content->getItem()->getStatements();
	}

}
