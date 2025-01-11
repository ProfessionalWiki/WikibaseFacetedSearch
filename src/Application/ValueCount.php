<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

class ValueCount {

	public function __construct(
		public readonly string|float|int $value,
		public readonly int $count,
	) {
	}

}
