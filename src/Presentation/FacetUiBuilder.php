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
				'values-html' => $this->getListFacetHtml( $this->getExampleBooleanItems() )
			],
			[
				'label' => 'Author',
				'type' => FacetType::LIST->value,
				'values-html' => $this->getListFacetHtml( $this->getExampleListItems() )
			],
			[
				'label' => 'Year',
				'type' => FacetType::RANGE->value,
				'values-html' => $this->getRangeFacetHtml( currentMin: 1900, currentMax: 2024 )
			],
			[
				'label' => 'Pages',
				'type' => FacetType::RANGE->value,
				'values-html' => $this->getRangeFacetHtml( currentMin: 10 )
			]
		];
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	private function getExampleBooleanItems(): array {
		return [
			[
				'label' => 'Yes',
				'count' => 42,
				'url' => 'https://example.com/facet/Yes',
				'selected' => true
			],
			[
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
	 * @param array<array<string, mixed>> $items
	 */
	private function getListFacetHtml( array $items ): string {
		return $this->parser->processTemplate(
			'ListFacet',
			[ 'items' => $items ]
		);
	}

	private function getRangeFacetHtml( ?int $currentMin = null, ?int $currentMax = null ): string {
		return $this->parser->processTemplate(
			'RangeFacet',
			[
				'msg-min' => wfMessage( 'wikibase-faceted-search-facet-range-min' )->text(),
				'msg-max' => wfMessage( 'wikibase-faceted-search-facet-range-max' )->text(),
				'current-min' => $currentMin,
				'current-max' => $currentMax,
			]
		);
	}

}
