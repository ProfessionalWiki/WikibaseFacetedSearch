<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeExtractor;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Statement\StatementList;

class StubItemTypeExtractor extends ItemTypeExtractor {

	private ?ItemId $itemType;

	public function __construct( ?ItemId $itemType = null ) {
		$this->itemType = $itemType;
	}

	public function getItemType( StatementList $statements ): ?ItemId {
		return $this->itemType;
	}

} 