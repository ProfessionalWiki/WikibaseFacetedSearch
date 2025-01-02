<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use TemplateParser;
use Wikibase\DataModel\Entity\ItemId;

class FacetUiBuilder {

	public function __construct(
		private readonly TemplateParser $parser,
		private readonly Config $config // TODO: use
	) {
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
				'type' => FacetType::LIST->value,
				'values-html' => $this->getItemsHtml( $this->getExampleBooleanItems() )
			],
			[
				'label' => 'Author',
				'type' => FacetType::LIST->value,
				'values-html' => $this->getItemsHtml( $this->getExampleListItems() )
			],
			[
				'label' => 'Year',
				'type' => FacetType::RANGE->value,
				'values-html' => $this->getItemsHtml( [ $this->getExampleRangeItems()[0] ] )
			],
			[
				'label' => 'Pages',
				'type' => FacetType::RANGE->value,
				'values-html' => $this->getItemsHtml( [ $this->getExampleRangeItems()[1] ] )
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
				'type' => 'Checkbox',
				'label' => 'Alice', // TODO: lookup of label and URL for item-id (or property-id) typed values
				'count' => 42,
				'url' => 'https://example.com/facet/Alice',
				'selected' => false
			],
			[
				'type' => 'Checkbox',
				'label' => 'Bob',
				'count' => 23,
				'url' => 'https://example.com/facet/Bob',
				'selected' => true
			],
			[
				'type' => 'Checkbox',
				'label' => 'Charlie',
				'count' => 17,
				'url' => 'https://example.com/facet/Charlie',
				'selected' => false
			],
			[
				'type' => 'Checkbox',
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
				'type' => 'Range',
				'msg-min' => wfMessage( 'wikibase-faceted-search-facet-range-min' )->text(),
				'msg-max' => wfMessage( 'wikibase-faceted-search-facet-range-max' )->text(),
				'current-min' => 1900,
				'current-max' => 2024,
			],
			[
				'type' => 'Range',
				'msg-min' => wfMessage( 'wikibase-faceted-search-facet-range-min' )->text(),
				'msg-max' => wfMessage( 'wikibase-faceted-search-facet-range-max' )->text(),
				'current-min' => 10
			]
		];
	}

	/**
	 * @param array<array<string, mixed>> $items
	 */
	private function getItemsHtml( array $items ): string {
		if ( $items === [] ) {
			return '';
		}

		$html = '';
		foreach ( $items as $item ) {
			if (
				!isset( $item['type'] ) ||
				!is_string( $item['type'] ) ||
				!strlen( $item['type'] ) > 0
			) {
				continue;
			}

			$html .= $this->parser->processTemplate(
				'FacetItem' . $item['type'],
				$item
			);
		}
		return $html;
	}

}
