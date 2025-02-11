<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Html\TemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;

/**
 * Renders list range via the `RangeFacet.mustache` template.
 */
class RangeFacetHtmlBuilder implements FacetHtmlBuilder {

	public function __construct(
		private readonly TemplateParser $parser,
	) {
	}

	public function buildHtml( FacetConfig $config, PropertyConstraints $state ): string {
		return $this->parser->processTemplate(
			'RangeFacet',
			[
				'msg-min' => wfMessage( 'wikibase-faceted-search-facet-range-min' )->text(),
				'msg-max' => wfMessage( 'wikibase-faceted-search-facet-range-max' )->text(),
				'msg-apply' => wfMessage( 'wikibase-faceted-search-facet-apply' )->text(),
				'current-min' => $state->getInclusiveMinimum(),
				'current-max' => $state->getInclusiveMaximum()
			]
		);
	}

}
