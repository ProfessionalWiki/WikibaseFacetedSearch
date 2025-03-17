<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\StatementsLookup;
use Psr\Log\LoggerInterface;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Services\Lookup\EntityLookup;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\Lib\Store\SiteLinkLookup;
use WikiPage;

class SitelinkBasedStatementsLookup implements StatementsLookup {

	public function __construct(
		private readonly string $sitelinkSiteId,
		private readonly SiteLinkLookup $sitelinkLookup,
		private readonly EntityLookup $entityLookup,
		private readonly LoggerInterface $logger
	) {
	}

	public function getStatements( WikiPage $page ): StatementList {
		$this->logger->debug( 'Getting statements for page: ' . $page->getTitle()->getPrefixedText() );

		$itemId = $this->sitelinkLookup->getItemIdForLink(
			$this->sitelinkSiteId,
			$page->getTitle()->getPrefixedText()
		);

		$this->logger->debug( 'sitelinkSiteId: ' . $this->sitelinkSiteId );
		$this->logger->debug( 'itemId: ' . ( $itemId?->getSerialization() ?? 'MISSING' ) );

		if ( $itemId === null ) {
			return new StatementList();
		}

		$entity = $this->entityLookup->getEntity( $itemId );

		$this->logger->debug( 'entity type: ' . ( $entity?->getType() ?? 'MISSING' ) );

		if ( !( $entity instanceof Item ) ) {
			return new StatementList();
		}

		$statements = $entity->getStatements();

		$this->logger->debug( 'statements count: ' . count( $statements ) );

		return $statements;
	}

}
