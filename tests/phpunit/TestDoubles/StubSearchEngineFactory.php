<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use SearchEngine;
use SearchEngineFactory;

class StubSearchEngineFactory extends SearchEngineFactory {

	public function __construct(
		private readonly SearchEngine $searchEngine
	) {
	}

	/**
	 * @param string|null $type
	 */
	public function create( $type = null ): SearchEngine {
		return $this->searchEngine;
	}

}
