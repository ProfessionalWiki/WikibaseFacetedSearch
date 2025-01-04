<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use InvalidArgumentException;
use MediaWiki\Parser\Sanitizer;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use TemplateParser;
use Wikibase\DataModel\Entity\ItemId;

class FacetUiBuilder {

	private array $facetTemplates = [];

	public function __construct(
		private readonly TemplateParser $parser,
		private readonly Config $config // TODO: use
	) {
		// TODO: Perhaps we should do a map for template name and FacetType
		// TODO: Should this go into FacetType?
		$this->facetTemplates = [
			FacetType::BOOLEAN->value => 'Radio',
			FacetType::LIST->value => 'Checkbox',
			FacetType::RANGE->value => 'Range'
		];
	}

	// TODO: parameter or constructor argument: values and counts (from https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/23)
	// TODO: parameter: selected values (from QueryStringParser https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/31)
	public function createHtml( ItemId $itemType ): string {
		$this->config->getFacetConfigForInstanceType( $itemType );

		return $this->parser->processTemplate(
			'Facets',
			[ 'facets' => $this->facetsToViewModel() ]
		);
	}

	/**
	 * @return array<array<string, string>>
	 */
	private function facetsToViewModel( /* TODO: parameters */ ): array {
		return [
			[
				'label' => 'Has Author',
				'type' => FacetType::BOOLEAN->value,
				'values-html' => $this->getItemsHtml(
					$this->getExampleBooleanItems(), FacetType::BOOLEAN->value, 'Has Author'
				)
			],
			[
				'label' => 'Author',
				'type' => FacetType::LIST->value,
				'values-html' => $this->getItemsHtml(
					$this->getExampleListItems(), FacetType::LIST->value, 'Author'
				)
			],
			[
				'label' => 'Year',
				'type' => FacetType::RANGE->value,
				'values-html' => $this->getItemsHtml(
					[ $this->getExampleRangeItems()[0] ], FacetType::RANGE->value, 'Year'
				)
			],
			[
				'label' => 'Pages',
				'type' => FacetType::RANGE->value,
				'values-html' => $this->getItemsHtml(
					[ $this->getExampleRangeItems()[1] ], FacetType::RANGE->value, 'Pages'
				)
			]
		];
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	private function getExampleBooleanItems(): array {
		return [
			[
				'type' => 'Radio',
				'name' => 'Has Author',
				'label' => 'Yes',
				'count' => 42,
				'url' => 'https://example.com/facet/Yes',
				'selected' => true
			],
			[
				'type' => 'Radio',
				'name' => 'Has Author',
				'label' => 'No',
				'count' => 23,
				'url' => 'https://example.com/facet/No',
				'selected' => false
			]
		];
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	private function getExampleListItems(): array {
		return [
			[
				'label' => 'Alice', // TODO: lookup of label and URL for item-id (or property-id) typed values
				'count' => 42,
				'url' => 'https://example.com/facet/Alice',
				'selected' => false
			],
			[
				'label' => 'Bob',
				'count' => 23,
				'url' => 'https://example.com/facet/Bob',
				'selected' => true
			],
			[
				'label' => 'Charlie',
				'count' => 17,
				'url' => 'https://example.com/facet/Charlie',
				'selected' => false
			],
			[
				'label' => 'David',
				'count' => 9,
				'url' => 'https://example.com/facet/David',
				'selected' => true
			]
		];
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	private function getExampleRangeItems(): array {
		return [
			[
				'msg-min' => wfMessage( 'wikibase-faceted-search-facet-range-min' )->text(),
				'msg-max' => wfMessage( 'wikibase-faceted-search-facet-range-max' )->text(),
				'current-min' => 1900,
				'current-max' => 2024,
			],
			[
				'msg-min' => wfMessage( 'wikibase-faceted-search-facet-range-min' )->text(),
				'msg-max' => wfMessage( 'wikibase-faceted-search-facet-range-max' )->text(),
				'current-min' => 10
			]
		];
	}

	/**
	 * @param array<array<string, mixed>> $items
	 */
	private function getItemsHtml( array $items, string $type, string $facetName ): string {
		if ( $items === [] ) {
			return '';
		}

		if ( !array_key_exists( $type, $this->facetTemplates ) ) {
			throw new InvalidArgumentException( "Missing template for facet type: $type" );
		}

		$html = '';
		$template = 'FacetItem' . $this->facetTemplates[$type];

		foreach ( $items as $i => $item ) {
			$item['id'] = Sanitizer::escapeIdForAttribute( htmlspecialchars( "$facetName-$i" ) );
			try {
				$html .= $this->parser->processTemplate( $template, $item );
			} catch ( RuntimeException ) {
				// ignore
			}
		}

		return $html;
	}

}
