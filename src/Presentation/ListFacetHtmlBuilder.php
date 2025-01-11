<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Html\TemplateParser;
use MediaWiki\Parser\Sanitizer;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCount;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounter;

/**
 * Renders list facets via the `ListFacet.mustache` template.
 */
class ListFacetHtmlBuilder implements FacetHtmlBuilder {

	public function __construct(
		private readonly TemplateParser $parser,
		private readonly ValueCounter $valueCounter
	) {
	}

	public function buildHtml( FacetConfig $config, PropertyConstraints $state ): string {
		return $this->parser->processTemplate(
			'ListFacet',
			[
				'hasToggle' => true, // TODO: use config allowCombineWithChoice and defaultCombineWith
				'checkboxes' => $this->buildCheckboxesViewModel( $config, $state ),
				'msg-and' => wfMessage( 'wikibase-faceted-search-and' )->text(),
				'msg-or' => wfMessage( 'wikibase-faceted-search-or' )->text(),
				// TODO: act on config: showNoneFilter
				// TODO: act on config: showAnyFilter
			]
		);
	}

	private function buildCheckboxesViewModel( FacetConfig $config, PropertyConstraints $state ): array {
		$combineWithAnd = true; // TODO: use state and config allowCombineWithChoice and defaultCombineWith
		$selectedValues = $combineWithAnd ? $state->getAndSelectedValues() : $state->getOrSelectedValues();

		$checkboxes = [];

		foreach ( $this->getValuesAndCounts( $config ) as $i => $valueCount ) {
			$checkboxes[] = [
				'label' => $valueCount->value,
				'count' => $valueCount->count, // FIXME: count is now showing in the UI for some reason
				'checked' => in_array( $valueCount->value, $selectedValues ), // TODO: test with multiple types of values
				'link' => 'TODO', // TODO: link values (probably need new collaborator for URL stuff, see SearchUrlBuilder)

				// TODO: can't we escape this in the template?
				'id' => Sanitizer::escapeIdForAttribute( htmlspecialchars( $state->propertyId->getSerialization() . "-$i" ) ),
			];
		}

		return $checkboxes;
	}

	/**
	 * @return ValueCount[]
	 */
	private function getValuesAndCounts( FacetConfig $config ): array {
		return $this->valueCounter->countValues( $config->propertyId )->asArray();
	}

}
