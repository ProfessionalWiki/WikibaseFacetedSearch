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
	private const CONFIG_KEY_SHOW_ANY_FILTER = 'showAnyFilter';
	private const CONFIG_KEY_SHOW_NONE_FILTER = 'showNoneFilter';

	public const CONFIG_VALUE_COMBINE_WITH_AND = 'AND';
	public const CONFIG_VALUE_COMBINE_WITH_OR = 'OR';
	public const CONFIG_VALUE_SHOW_ANY_FILTER = 'ANY';
	public const CONFIG_VALUE_SHOW_NONE_FILTER = 'NONE';

	public function __construct(
		private readonly TemplateParser $parser,
		private readonly ValueCounter $valueCounter,
		private readonly FacetValueFormatter $valueFormatter
	) {
	}

	public function buildHtml( FacetConfig $config, PropertyConstraints $state ): string {
		$valueCounts = $this->getValuesAndCounts( $state );
		if ( count( $valueCounts ) === 0 && !$state->hasAnyValue() && !$state->hasNoValue() ) {
			return '';
		}

		return $this->parser->processTemplate(
			'ListFacet',
			$this->buildViewModel( $config, $state, $valueCounts )
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function buildViewModel( FacetConfig $config, PropertyConstraints $state, array $valueCounts ): array {
		$facetMode = $this->getFacetMode( $config, $state );

		return [
			'select' => $this->buildModeSelectViewModel( $config, $facetMode ),
			'checkboxes' => $this->buildCheckboxesViewModel( $config, $state, $valueCounts, $facetMode ),
			'msg-show-more' => wfMessage( 'wikibase-faceted-search-facet-show-more' )->text(),
			'msg-show-less' => wfMessage( 'wikibase-faceted-search-facet-show-less' )->text()
		];
	}

	public function getFacetMode( FacetConfig $config, PropertyConstraints $state ): string {
		if ( $state->hasAnyValue() ) {
			return self::CONFIG_VALUE_SHOW_ANY_FILTER;
		}

		if ( $state->hasNoValue() ) {
			return self::CONFIG_VALUE_SHOW_NONE_FILTER;
		}

		if ( $this->hasCombineWithChoice( $config ) ) {
			if ( $state->getOrSelectedValues() !== [] ) {
				return self::CONFIG_VALUE_COMBINE_WITH_OR;
			}

			if ( $state->getAndSelectedValues() !== [] ) {
				return self::CONFIG_VALUE_COMBINE_WITH_AND;
			}
		}

		return $this->getDefaultCombineWithValue( $config );
	}

	private function getDefaultCombineWithValue( FacetConfig $config ): string {
		if ( ( $config->typeSpecificConfig[self::CONFIG_KEY_DEFAULT_COMBINE_WITH] ?? null ) === self::CONFIG_VALUE_COMBINE_WITH_OR ) {
			return self::CONFIG_VALUE_COMBINE_WITH_OR;
		}

		if ( ( $config->typeSpecificConfig[self::CONFIG_KEY_DEFAULT_COMBINE_WITH] ?? null ) === self::CONFIG_VALUE_COMBINE_WITH_AND ) {
			return self::CONFIG_VALUE_COMBINE_WITH_AND;
		}

		return self::CONFIG_VALUE_COMBINE_WITH_AND;
	}

	private function hasCombineWithChoice( FacetConfig $config ): bool {
		return (bool)( $config->typeSpecificConfig[self::CONFIG_KEY_ALLOW_COMBINE_WITH_CHOICE] ?? true );
	}

	private function buildModeSelectViewModel( FacetConfig $config, string $facetMode ): array {
		$canChoose = $this->hasCombineWithChoice( $config );
		$defaultCombineWithValue = $this->getDefaultCombineWithValue( $config );

		$options = [
			[
				'label' => wfMessage( 'wikibase-faceted-search-facet-all-selected-values' )->text(),
				'value' => self::CONFIG_VALUE_COMBINE_WITH_AND,
				'selected' => $facetMode === self::CONFIG_VALUE_COMBINE_WITH_AND,
				'disabled' => !$canChoose && $defaultCombineWithValue !== self::CONFIG_VALUE_COMBINE_WITH_AND
			],
			[
				'label' => wfMessage( 'wikibase-faceted-search-facet-any-selected-values' )->text(),
				'value' => self::CONFIG_VALUE_COMBINE_WITH_OR,
				'selected' => $facetMode === self::CONFIG_VALUE_COMBINE_WITH_OR,
				'disabled' => !$canChoose && $defaultCombineWithValue !== self::CONFIG_VALUE_COMBINE_WITH_OR
			],
			[
				'label' => wfMessage( 'wikibase-faceted-search-facet-any-value' )->text(),
				'value' => self::CONFIG_VALUE_SHOW_ANY_FILTER,
				'selected' => $facetMode === self::CONFIG_VALUE_SHOW_ANY_FILTER,
				'disabled' => !$this->hasAnyValueFilter( $config )
			],
			[
				'label' => wfMessage( 'wikibase-faceted-search-facet-no-value' )->text(),
				'value' => self::CONFIG_VALUE_SHOW_NONE_FILTER,
				'selected' => $facetMode === self::CONFIG_VALUE_SHOW_NONE_FILTER,
				'disabled' => !$this->hasNoValueFilter( $config )
			]
		];

		return [
			'defaultValue' => $facetMode,
			'options' => array_values( array_filter(
				$options,
				fn( $option ) => !$option['disabled']
			) )
		];
	}

	private function hasAnyValueFilter( FacetConfig $config ): bool {
		return (bool)( $config->typeSpecificConfig[self::CONFIG_KEY_SHOW_ANY_FILTER] ?? true );
	}

	private function hasNoValueFilter( FacetConfig $config ): bool {
		return (bool)( $config->typeSpecificConfig[self::CONFIG_KEY_SHOW_NONE_FILTER] ?? true );
	}

	private function buildCheckboxesViewModel( FacetConfig $config, PropertyConstraints $state, array $valueCounts, string $facetMode ): array {
		if (
			$valueCounts === [] ||
			$facetMode === self::CONFIG_VALUE_SHOW_ANY_FILTER ||
			$facetMode === self::CONFIG_VALUE_SHOW_NONE_FILTER
		) {
			return [
				'visible' => [],
				'collapsed' => [],
				'showMore' => false
			];
		}

		$maxVisibleCheckboxes = 5; // TODO: Make this configurable

		$selectedValues = array_filter(
			$facetMode === self::CONFIG_VALUE_COMBINE_WITH_AND ? $state->getAndSelectedValues() : $state->getOrSelectedValues(),
			fn( $value ) => $value !== ''
		);

		$checkboxes = [];

		foreach ( $valueCounts as $i => $valueCount ) {
			$checkboxes[] = $this->buildCheckboxViewModel( $config, $valueCount, $selectedValues, $state->propertyId, $i );
		}

		usort( $checkboxes, fn( $a, $b ) => $b['checked'] <=> $a['checked'] );

		return [
			'visible' => array_slice( $checkboxes, 0, $maxVisibleCheckboxes ),
			'collapsed' => array_slice( $checkboxes, $maxVisibleCheckboxes ),
			'showMore' => count( $checkboxes ) > $maxVisibleCheckboxes
		];
	}

	private function buildCheckboxViewModel( FacetConfig $config, ValueCount $valueCount, array $selectedValues, PropertyId $propertyId, int $index ): array {
		return [
			'formattedValue' => $this->valueFormatter->getLabel( (string)$valueCount->value, $config->propertyId ),
			'count' => $valueCount->count,
			'checked' => in_array( $valueCount->value, $selectedValues ), // TODO: test with multiple types of values
			'value' => $valueCount->value,
			'id' => $propertyId->getSerialization() . "-$index",
		];
	}

	/**
	 * @return ValueCount[]
	 */
	public function getValuesAndCounts( PropertyConstraints $state ): array {
		return $this->valueCounter->countValues( $state )->asArray();
	}

}
