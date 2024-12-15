<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemPageLookup;
use Title;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\SiteLink;
use Wikibase\Lib\Store\SiteLinkLookup;

class SiteLinkItemPageLookup implements ItemPageLookup {

	public function __construct(
		private readonly SiteLinkLookup $siteLinksStore,
		private readonly string $siteLinkSiteId
	) {
	}

	public function getPageTitle( ItemId $itemId ): ?Title {
		$siteLink = $this->getConfiguredSiteLink( $this->siteLinksStore->getSiteLinksForItem( $itemId ) );

		if ( $siteLink === null ) {
			return null;
		}

		return Title::newFromText( $siteLink->getPageName() );
	}

	/**
	 * @param SiteLink[] $siteLinks
	 */
	private function getConfiguredSiteLink( array $siteLinks ): ?SiteLink {
		return array_filter(
			$siteLinks,
			fn( $siteLink ) => $siteLink->getSiteId() === $this->siteLinkSiteId
		)[0] ?? null;
	}

}
