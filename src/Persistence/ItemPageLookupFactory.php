<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use Wikibase\Repo\WikibaseRepo;

class ItemPageLookupFactory {

	public function __construct(
		private readonly Config $config
	) {
	}

	public function newItemPageLookup(): ItemPageLookup {
		if ( $this->config->linkTargetSitelinkSiteId !== null ) {
			return new SiteLinkItemPageLookup(
				WikibaseRepo::getStore()->newSiteLinkStore(),
				$this->config->linkTargetSitelinkSiteId
			);
		}

		return new NullItemPageLookup();
	}
}
