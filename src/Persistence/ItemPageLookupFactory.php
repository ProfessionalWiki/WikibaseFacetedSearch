<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemPageLookup;
use Wikibase\Repo\WikibaseRepo;

class ItemPageLookupFactory {

	public function __construct(
		private readonly Config $config
	) {
	}

	public function newItemPageLookup(): ItemPageLookup {
		if ( $this->config->linkTargetSitelinkSiteId !== null ) {
			return new SitelinkItemPageLookup(
				WikibaseRepo::getStore()->newSiteLinkStore(),
				$this->config->linkTargetSitelinkSiteId
			);
		}

		return new NullItemPageLookup();
	}
}
