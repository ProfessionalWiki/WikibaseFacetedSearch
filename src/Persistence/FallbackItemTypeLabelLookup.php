<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\MessageBuilder\MessageBuilder;
use ProfessionalWiki\MessageBuilder\UnknownMessageKey;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeLabelLookup;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\Lookup\LabelLookup;

class FallbackItemTypeLabelLookup implements ItemTypeLabelLookup {

	public function __construct(
		private readonly LabelLookup $labelLookup,
		private readonly MessageBuilder $messageBuilder,
	) {
	}

	public function getLabel( ItemId $itemType ): string {
		try {
			return $this->getMessage( $itemType );
		}
		catch ( UnknownMessageKey ) {
			return $this->labelLookup->getLabel( $itemType )?->getText() ?? $itemType->getSerialization();
		}
	}

	private function getMessage( ItemId $itemType ): string {
		return $this->messageBuilder->buildMessage( 'WikibaseFacetedSearch-item-type-' . $itemType->getSerialization() );
	}

}
