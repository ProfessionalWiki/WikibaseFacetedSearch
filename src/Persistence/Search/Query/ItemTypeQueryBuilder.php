<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query;

use Elastica\Query;
use Elastica\Query\AbstractQuery;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

class ItemTypeQueryBuilder {

	public function __construct(
		private readonly PropertyId $itemTypeProperty
	) {
	}

	public function buildQuery( array $itemTypes ): AbstractQuery {
		return new Query\Terms(
			'wbfs_' . $this->itemTypeProperty->getSerialization(),
			array_map(
				fn( ItemId $itemType ) => $itemType->getSerialization(),
				$itemTypes
			)
		);
	}

}
