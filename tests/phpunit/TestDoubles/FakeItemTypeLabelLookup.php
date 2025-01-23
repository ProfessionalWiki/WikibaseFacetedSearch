<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeLabelLookup;
use Wikibase\DataModel\Entity\ItemId;

class FakeItemTypeLabelLookup implements ItemTypeLabelLookup {

	public function getLabel( ItemId $itemType ): string {
		return $itemType->getSerialization() . 'Label';
	}

}
