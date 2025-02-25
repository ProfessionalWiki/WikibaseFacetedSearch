<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemPageUpdater;
use Wikibase\DataModel\Entity\Item;

class SitelinkItemPageUpdater implements ItemPageUpdater {

	public function __construct(
		private readonly string $sitelinkSiteId,
		private readonly WikiPageFactory $pageFactory
	) {
	}

	public function updatePage( Item $item, UserIdentity $user ): void {
		$title = $this->getSitelinkedTitle( $item );

		if ( $title === null ) {
			return;
		}

		$this->pageFactory->newFromTitle( $title )
			->newPageUpdater( $user )
			->updateRevision();
	}

	private function getSitelinkedTitle( Item $item ): ?Title {
		if ( $item->hasLinkToSite( $this->sitelinkSiteId ) ) {
			return Title::newFromText( $item->getSiteLink( $this->sitelinkSiteId )->getPageName() );
		}

		return null;
	}

}
