<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use TemplateParser;

class FacetUiBuilder {

	public function __construct(
		private readonly TemplateParser $parser
	) {
	}

	public function createHtml( /* TODO: FacetList */ ): string {
		return $this->parser->processTemplate(
			'Facets',
			[ 'facets' => $this->facetsToViewModel() ]
		);
	}

	/**
	 * @return array<array<string, string>>
	 */
	private function facetsToViewModel( /* TODO: FacetList */ ): array {
		return [
			[
				'label' => 'Has Author',
				'type' => 'list',
				'values-html' => $this->getListFacetHtml( $this->getExampleBooleanItems() )
			],
			[
				'label' => 'Author',
				'type' => 'list',
				'values-html' => $this->getListFacetHtml( $this->getExampleListItems() )
			],
			[
				'label' => 'Year',
				'type' => 'range',
				'values-html' => $this->getRangeFacetHtml( currentMin: 1900, currentMax: 2024 )
			],
			[
				'label' => 'Pages',
				'type' => 'range',
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
				'label' => 'Alice',
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
