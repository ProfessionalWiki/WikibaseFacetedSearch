<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use MediaWiki\Title\Title;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemPageLookup;
use Wikibase\DataModel\Entity\ItemId;

class NullItemPageLookup implements ItemPageLookup {

	public function getPageTitle( ItemId $itemId ): ?Title {
		return null;
	}

}
