<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeLabelLookup;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\Lookup\LabelLookup;

// TODO: Make into a generic class that can be used for other labels
class FallbackItemTypeLabelLookup implements ItemTypeLabelLookup {

	public function __construct(
		private readonly LabelLookup $labelLookup,
	) {
	}

	public function getLabel( ItemId $itemType ): string {
		// TODO: prefer item from internationalized source https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/91

		return $this->labelLookup->getLabel( $itemType )?->getText() ?? $itemType->getSerialization();
	}

}
