<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\StatementsLookup;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Services\Lookup\EntityLookup;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\Lib\Store\SiteLinkLookup;
use WikiPage;

class SitelinkBasedStatementsLookup implements StatementsLookup {

	public function __construct(
		private readonly string $sitelinkSiteId,
		private readonly SiteLinkLookup $sitelinkLookup,
		private readonly EntityLookup $entityLookup
	) {
	}

	public function getStatements( WikiPage $page ): StatementList {
		$itemId = $this->sitelinkLookup->getItemIdForLink(
			$this->sitelinkSiteId,
			$page->getTitle()->getText()
		);

		if ( $itemId === null ) {
			return new StatementList();
		}

		$entity = $this->entityLookup->getEntity( $itemId );

		if ( !( $entity instanceof Item ) ) {
			return new StatementList();
		}

		return $entity->getStatements();
	}

}
