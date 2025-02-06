<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Elastica\Query\AbstractQuery;

class SearchQueryHistory {

	/**
	 * @var array<string, AbstractQuery>
	 */
	private array $queriesByTerm = [];

	public function setQuery( string $term, AbstractQuery $query ): void {
		$this->queriesByTerm[$term] = $query;
	}

	public function getQuery( string $term ): ?AbstractQuery {
		return $this->queriesByTerm[$term] ?? null;
	}
}
