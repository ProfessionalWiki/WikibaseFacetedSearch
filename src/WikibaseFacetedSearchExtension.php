<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch;

class WikibaseFacetedSearchExtension {

	public static function getInstance(): self {
		/** @var ?WikibaseFacetedSearchExtension $instance */
		static $instance = null;
		$instance ??= new self();
		return $instance;
	}

}
