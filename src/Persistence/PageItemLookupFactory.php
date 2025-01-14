<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PageItemLookup;
use Wikibase\Lib\Store\SiteLinkLookup;

class PageItemLookupFactory {

	public function __construct(
		private readonly Config $config,
		private readonly SiteLinkLookup $sitelinkLookup
	) {
	}

	public function newPageItemLookup(): PageItemLookup {
		if ( $this->config->linkTargetSitelinkSiteId !== null ) {
			return new SitelinkPageItemLookup(
				$this->sitelinkLookup,
				$this->config->linkTargetSitelinkSiteId
			);
		}

		return new NullPageItemLookup();
	}
}
