<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Title;
use Wikibase\DataModel\Entity\ItemId;

interface ItemPageLookup {

	public function getPageTitle( ItemId $itemId ): ?Title;

}
