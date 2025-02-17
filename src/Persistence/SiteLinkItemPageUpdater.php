<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemPageUpdater;
use Wikibase\DataModel\Entity\Item;
use WikiPage;

class SiteLinkItemPageUpdater implements ItemPageUpdater {

	public function __construct(
		private readonly string $linkTargetSitelinkSiteId,
		private readonly WikiPageFactory $pageFactory
	) {
	}

	public function updatePage( Item $item, UserIdentity $user ): void {
		if ( $item->hasLinkToSite( $this->linkTargetSitelinkSiteId ) ) {
			$this->getSiteLinkedPage( $item )
				->newPageUpdater( $user )
				->updateRevision();
		}
	}

	private function getSiteLinkedPage( Item $item ): WikiPage {
		return $this->pageFactory->newFromTitle(
			Title::newFromText( $item->getSiteLink( $this->linkTargetSitelinkSiteId )->getPageName() )
		);
	}

}
