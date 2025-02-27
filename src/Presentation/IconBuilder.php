<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

interface IconBuilder {

	public function buildHtml( string $iconName, ?array $options = [] ): string;

}
