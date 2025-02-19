<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeLabelLookup;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\Lookup\LabelLookup;

class FallbackItemTypeLabelLookup implements ItemTypeLabelLookup {

	public function __construct(
		private readonly LabelLookup $labelLookup,
	) {
	}

	public function getLabel( ItemId $itemType ): string {
		$message = wfMessage( 'WikibaseFacetedSearch-item-type-' . $itemType->getSerialization() );

		if ( $message->exists() ) {
			return $message->text();
		}

		return $this->labelLookup->getLabel( $itemType )?->getText() ?? $itemType->getSerialization();
	}

}
