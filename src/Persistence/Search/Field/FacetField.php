<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Field;

use CirrusSearch\CirrusSearch;
use SearchEngine;
use SearchIndexField;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Statement\StatementListProvider;
use Wikibase\Repo\Search\Fields\WikibaseIndexField;

class FacetField /*extends SearchIndexFieldDefinition*/ implements WikibaseIndexField {

	public function __construct(
		private readonly PropertyId $propertyId
	) {
	}

	public function getMappingField( SearchEngine $engine, $name ): ?SearchIndexField {
		if ( !( $engine instanceof CirrusSearch ) ) {
			return null;
		}

		// TODO: dedicated field
		return $engine->makeSearchFieldMapping(
			$name,
			\SearchIndexField::INDEX_TYPE_INTEGER
		);
	}

	public function getFieldData( EntityDocument $entity ) {
		if ( !( $entity instanceof StatementListProvider ) ) {
			return [];
		}

		$statements = $entity->getStatements()->getByPropertyId( $this->propertyId );

		// TODO
		return array_map(
			fn($i) => $i+1000,
			range(1, $statements->count())
		);
	}

}
