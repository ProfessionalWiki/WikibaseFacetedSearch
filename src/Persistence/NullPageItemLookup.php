<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use MediaWiki\Title\Title;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PageItemLookup;
use Wikibase\DataModel\Entity\ItemId;

class NullPageItemLookup implements PageItemLookup {

	public function getItemId( Title $title ): ?ItemId {
		return null;
	}

}
