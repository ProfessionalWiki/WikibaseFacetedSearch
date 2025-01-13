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

	/**
	 * TODO: add integration smoke test
	 * TODO: add unit tests for the logic. Likley requires some refactoring, ie making the view model accessible
	 */
	public function buildHtml( FacetConfig $config, PropertyConstraints $state ): string {
		$combineWithAnd = true; // TODO: use state and config defaultCombineWith

		return $this->parser->processTemplate(
			'ListFacet',
			[
				'toggle' => $this->buildToggleViewModel( $config, $state, $combineWithAnd ),
				'checkboxes' => $this->buildCheckboxesViewModel( $config, $state, $combineWithAnd ),
				// TODO: act on config: showNoneFilter
				// TODO: act on config: showAnyFilter
			]
		);
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	private function buildToggleViewModel( FacetConfig $config, PropertyConstraints $state, bool $combineWithAnd ): array {
		$disabled = true; // TODO: use state and config allowCombineWithChoice

		return [
			'and' => [
				'label' => wfMessage( 'wikibase-faceted-search-and' )->text(),
				'selected' => $combineWithAnd,
				// TODO: Remove the ignore when dynamic value for $combineWithAnd is implemented
				// @phpstan-ignore ternary.alwaysTrue
				'disabled' => $combineWithAnd === true ? false : $disabled
			],
			'or' => [
				'label' => wfMessage( 'wikibase-faceted-search-or' )->text(),
				'selected' => !$combineWithAnd,
				// TODO: Remove the ignore when dynamic value for $combineWithAnd is implemented
				// @phpstan-ignore ternary.alwaysTrue
				'disabled' => $combineWithAnd === false ? false : $disabled
			]
		];
	}

	private function buildCheckboxesViewModel( FacetConfig $config, PropertyConstraints $state, bool $combineWithAnd ): array {
		$selectedValues = $combineWithAnd ? $state->getAndSelectedValues() : $state->getOrSelectedValues();

		$checkboxes = [];

		foreach ( $this->getValuesAndCounts( $config ) as $i => $valueCount ) {
			$checkboxes[] = [
				'label' => $valueCount->value,
				'count' => $valueCount->count, // FIXME: count is now showing in the UI for some reason
				'checked' => in_array( $valueCount->value, $selectedValues ), // TODO: test with multiple types of values
				'link' => 'TODO', // TODO: remove link. Perhaps add some data-attribute (though currently "label" already gets the value)

				'value-id' => $valueCount->value,
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
