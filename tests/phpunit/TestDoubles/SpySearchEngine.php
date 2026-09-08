<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use MediaWiki\Status\Status;
use SearchEngine;

class SpySearchEngine extends SearchEngine {

	public string $searchedText = '';

	public ?int $requestedLimit = null;

	public function __construct(
		private readonly Status $searchResult
	) {
	}

	/**
	 * @param string $term
	 */
	protected function doSearchText( $term ): Status {
		$this->searchedText = $term;
		return $this->searchResult;
	}

	/**
	 * @param int $limit
	 * @param int $offset
	 */
	public function setLimitOffset( $limit, $offset = 0 ) {
		$this->requestedLimit = $limit;
		parent::setLimitOffset( $limit, $offset );
	}

	public function suggestionsRequested(): bool {
		return $this->showSuggestion;
	}

}
