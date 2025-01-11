<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

class ValueCounts {

	/**
	 * @param ValueCount[] $valueCounts
	 */
	public function __construct(
		private readonly array $valueCounts,
	) {
	}

	/**
	 * @return ValueCount[]
	 */
	public function asArray(): array {
		return $this->valueCounts;
	}

}
