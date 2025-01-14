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
			$this->buildViewModel( $config, $state )
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function buildViewModel( FacetConfig $config, PropertyConstraints $state ): array {
		$combineWithAnd = true; // TODO: use state and config defaultCombineWith

		return [
			'toggle' => $this->buildToggleViewModel( $config, $state, $combineWithAnd ),
			'checkboxes' => $this->buildCheckboxesViewModel( $config, $state, $combineWithAnd ),
			// TODO: act on config: showNoneFilter
			// TODO: act on config: showAnyFilter
		];
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	private function buildToggleViewModel( FacetConfig $config, PropertyConstraints $state, bool $combineWithAnd ): array {
		// $disabled = true; // TODO: use state and config allowCombineWithChoice

		return [
			'and' => [
				'label' => wfMessage( 'wikibase-faceted-search-and' )->text(),
				'selected' => $combineWithAnd,
				'disabled' => !$combineWithAnd // && $disabled
			],
			'or' => [
				'label' => wfMessage( 'wikibase-faceted-search-or' )->text(),
				'selected' => !$combineWithAnd,
				'disabled' => $combineWithAnd // && $disabled
			]
		];
	}

	private function buildCheckboxesViewModel( FacetConfig $config, PropertyConstraints $state, bool $combineWithAnd ): array {
		$selectedValues = $combineWithAnd ? $state->getAndSelectedValues() : $state->getOrSelectedValues();

		$checkboxes = [];

		foreach ( $this->getValuesAndCounts( $config ) as $i => $valueCount ) {
			$checkboxes[] = [
				'label' => $valueCount->value,
				'count' => $valueCount->count,
				'checked' => in_array( $valueCount->value, $selectedValues ), // TODO: test with multiple types of values
				'value' => $valueCount->value,
				// TODO: can't we escape this in the template?
				// https://github.com/ProfessionalWiki/WikibaseFacetedSearch/pull/95#discussion_r1912980729
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
