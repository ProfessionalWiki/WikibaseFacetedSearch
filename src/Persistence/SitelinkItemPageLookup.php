<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemPageLookup;
use Title;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\SiteLink;
use Wikibase\Lib\Store\SiteLinkLookup;

class SitelinkItemPageLookup implements ItemPageLookup {

	public function __construct(
		private readonly SiteLinkLookup $sitelinkLookup,
		private readonly string $sitelinkSiteId
	) {
	}

	public function getPageTitle( ItemId $itemId ): ?Title {
		$sitelink = $this->getConfiguredSitelink( $this->sitelinkLookup->getSiteLinksForItem( $itemId ) );

		if ( $sitelink === null ) {
			return null;
		}

		return Title::newFromText( $sitelink->getPageName() );
	}

	/**
	 * @param SiteLink[] $sitelinks
	 */
	private function getConfiguredSitelink( array $sitelinks ): ?SiteLink {
		return array_filter(
			$sitelinks,
			fn( SiteLink $sitelink ) => $sitelink->getSiteId() === $this->sitelinkSiteId
		)[0] ?? null;
	}

}
