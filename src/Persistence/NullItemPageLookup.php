<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemPageLookup;
use Title;
use Wikibase\DataModel\Entity\ItemId;

class NullItemPageLookup implements ItemPageLookup {

	public function getPageTitle( ItemId $itemId ): ?Title {
		return null;
	}

}
