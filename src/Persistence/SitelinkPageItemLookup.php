<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use MediaWiki\Title\Title;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PageItemLookup;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lib\Store\SiteLinkLookup;

class SitelinkPageItemLookup implements PageItemLookup {

	public function __construct(
		private readonly SiteLinkLookup $sitelinkLookup,
		private readonly string $sitelinkSiteId
	) {
	}

	public function getItemId( Title $title ): ?ItemId {
		return $this->sitelinkLookup->getItemIdForLink( $this->sitelinkSiteId, $title->getText() );
	}

}
