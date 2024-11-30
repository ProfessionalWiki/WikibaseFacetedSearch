<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use Title;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lib\Store\SiteLinkLookup;

class SiteLinkItemPageLookup implements ItemPageLookup {

	public function __construct(
		private readonly SiteLinkLookup $siteLinksStore,
		private readonly string $siteLinkSiteId
	) {
	}

	public function getPageTitle( ItemId $itemId ): ?Title {
		$siteLink = array_filter(
			$this->siteLinksStore->getSiteLinksForItem( $itemId ),
			fn( $siteLink ) => $siteLink->getSiteId() === $this->siteLinkSiteId
		)[0] ?? null;

		if ( $siteLink === null ) {
			return null;
		}

		return Title::newFromText( $siteLink->getPageName() );
	}

}
