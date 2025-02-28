<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use ProfessionalWiki\WikibaseFacetedSearch\Presentation\IconBuilder;

class StubIconBuilder implements IconBuilder {

	public function __construct(
		private readonly string $html = '<span class="test-icon"></span>'
	) {
	}

	public function buildHtml( string $iconName, ?array $options = [] ): string {
		return $this->html;
	}

}
