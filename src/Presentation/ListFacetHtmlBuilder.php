<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Html\TemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCount;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounter;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Renders list facets via the `ListFacet.mustache` template.
 */
class ListFacetHtmlBuilder implements FacetHtmlBuilder {

	private const CONFIG_KEY_ALLOW_COMBINE_WITH_CHOICE = 'allowCombineWithChoice';
	private const CONFIG_KEY_DEFAULT_COMBINE_WITH = 'defaultCombineWith';
	private const CONFIG_VALUE_COMBINE_WITH_AND = 'AND';
	private const CONFIG_VALUE_COMBINE_WITH_OR = 'OR';

	private const COMBINE_WITH_AND_BY_DEFAULT = true; // Maybe this gets turned into (constructor-injected) config

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
		$combineWithAnd = $this->shouldCombineWithAnd( $config, $state );

		return [
			'toggle' => $this->buildToggleViewModel( $combineWithAnd, $this->hasCombineWithChoice( $config ) ),
			'checkboxes' => $this->buildCheckboxesViewModel( $config, $state ),
			'msg-show-more' => wfMessage( 'wikibase-faceted-search-facet-show-more' )->text(),
			'msg-show-less' => wfMessage( 'wikibase-faceted-search-facet-show-less' )->text()
			// TODO: act on config: showNoneFilter https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/117
			// TODO: act on config: showAnyFilter https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/119
		];
	}

	public function shouldCombineWithAnd( FacetConfig $config, PropertyConstraints $state ): bool {
		if ( $this->hasCombineWithChoice( $config ) ) {
			if ( $state->getOrSelectedValues() !== [] ) {
				return false;
			}

			if ( $state->getAndSelectedValues() !== [] ) {
				return true;
			}
		}

		if ( ( $config->typeSpecificConfig[self::CONFIG_KEY_DEFAULT_COMBINE_WITH] ?? null ) === self::CONFIG_VALUE_COMBINE_WITH_OR ) {
			return false;
		}

		if ( ( $config->typeSpecificConfig[self::CONFIG_KEY_DEFAULT_COMBINE_WITH] ?? null ) === self::CONFIG_VALUE_COMBINE_WITH_AND ) {
			return true;
		}

		return self::COMBINE_WITH_AND_BY_DEFAULT;
	}

	private function hasCombineWithChoice( FacetConfig $config ): bool {
		return (bool)( $config->typeSpecificConfig[self::CONFIG_KEY_ALLOW_COMBINE_WITH_CHOICE] ?? false );
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	private function buildToggleViewModel( bool $combineWithAnd, bool $canChoose ): array {
		return [
			'and' => [
				'label' => wfMessage( 'wikibase-faceted-search-and' )->text(),
				'selected' => $combineWithAnd,
				'disabled' => !$canChoose && !$combineWithAnd
			],
			'or' => [
				'label' => wfMessage( 'wikibase-faceted-search-or' )->text(),
				'selected' => !$combineWithAnd,
				'disabled' => !$canChoose && $combineWithAnd
			]
		];
	}

	private function buildCheckboxesViewModel( FacetConfig $config, PropertyConstraints $state ): array {
		$maxVisibleCheckboxes = 5; // TODO: Make this configurable
		$combineWithAnd = $this->shouldCombineWithAnd( $config, $state );

		$selectedValues = $combineWithAnd ? $state->getAndSelectedValues() : $state->getOrSelectedValues();

		$checkboxes = [];

		foreach ( $this->getValuesAndCounts( $config ) as $i => $valueCount ) {
			$checkboxes[] = $this->buildCheckboxViewModel( $valueCount, $selectedValues, $state->propertyId, $i );
		}

		return [
			'visible' => array_slice( $checkboxes, 0, $maxVisibleCheckboxes ),
			'collapsed' => array_slice( $checkboxes, $maxVisibleCheckboxes ),
			'showMore' => count( $checkboxes ) > $maxVisibleCheckboxes
		];
	}

	private function buildCheckboxViewModel( ValueCount $valueCount, array $selectedValues, PropertyId $propertyId, int $index ): array {
		return [
			'label' => $valueCount->value,
			'count' => $valueCount->count,
			'checked' => in_array( $valueCount->value, $selectedValues ), // TODO: test with multiple types of values
			'value' => $valueCount->value,
			'id' => $propertyId->getSerialization() . "-$index",
		];
	}

	/**
	 * @return ValueCount[]
	 */
	private function getValuesAndCounts( FacetConfig $config ): array {
		return $this->valueCounter->countValues( $config->propertyId )->asArray();
	}

}
