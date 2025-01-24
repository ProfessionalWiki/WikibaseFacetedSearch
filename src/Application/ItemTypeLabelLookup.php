<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\EntityId;

// TODO: Make into a generic class that can be used for other labels
interface ItemTypeLabelLookup {

	public function getLabel( EntityId $itemType ): string;

}
