<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\ItemId;

interface ItemTypeLabelLookup {

	public function getLabel( ItemId $itemType ): string;

}
