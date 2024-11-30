<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch;

use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ItemPageLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\SiteLinkItemPageLookup;
use Wikibase\Repo\WikibaseRepo;

class WikibaseFacetedSearchExtension {

	public static function getInstance(): self {
		/** @var ?WikibaseFacetedSearchExtension $instance */
		static $instance = null;
		$instance ??= new self();
		return $instance;
	}

	public function getItemPageLookup(): ItemPageLookup {
		return new SiteLinkItemPageLookup(
			WikibaseRepo::getStore()->newSiteLinkStore(),
			// TODO: https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/12
			'mardi'
		);
	}

}
