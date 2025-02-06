<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use Elastica\Query\AbstractQuery;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;

interface FacetHtmlBuilder {

	public function buildHtml( FacetConfig $config, PropertyConstraints $state, AbstractQuery $query ): string;

}
