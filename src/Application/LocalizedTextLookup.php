<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikibase\DataModel\Services\Lookup\LabelLookup;

class LocalizedTextLookup {

	public function __construct(
		private readonly EntityIdParser $entityIdParser,
		private readonly LabelLookup $labelLookup
	) {
	}

	public function getLabelFromEntityIdString( string $entityIdString ): string {
		try {
			$entityId = $this->entityIdParser->parse( $entityIdString );
		} catch ( EntityIdParsingException $e ) {
			return $entityIdString;
		}
		return $this->getLabelFromEntityId( $entityId );
	}

	public function getLabelFromEntityId( EntityId $entityId ): string {
		return $this->labelLookup->getLabel( $entityId )?->getText() ?? $entityId->getSerialization();
	}

}
