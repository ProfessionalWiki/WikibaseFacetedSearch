<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use MediaWiki\Title\Title;
use Wikibase\DataModel\Entity\ItemId;

interface PageItemLookup {

	public function getItemId( Title $title ): ?ItemId;

}
