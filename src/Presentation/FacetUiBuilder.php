<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use InvalidArgumentException;
use MediaWiki\Html\TemplateParser;
use MediaWiki\Parser\Sanitizer;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\Application\SearchUrlBuilder;
use RuntimeException;
use Wikibase\DataModel\Entity\ItemId;

class FacetUiBuilder {

	/** @var array<string, PropertyConstraints> */
	private array $constraints = [];

	public function __construct(
		private readonly TemplateParser $parser,
		private readonly QueryStringParser $queryStringParser,
		private readonly SearchUrlBuilder $searchUrlBuilder,
		private readonly Config $config, // TODO: use
	) {
	}

	// TODO: parameter or constructor argument: values and counts (from https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/23)
	// TODO: parameter: selected values (from QueryStringParser https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/31)
	public function createHtml( ItemId $itemType, string $url ): string {
		$this->config->getFacetConfigForInstanceType( $itemType );

		$this->searchUrlBuilder->setUrl( $url );

		$query = $this->queryStringParser->parse( $this->searchUrlBuilder->getUrlQuery()['search'] );
		$this->constraints = $query->getConstraintsPerProperty();

		return $this->parser->processTemplate(
			'Facets',
			[
				'msg-filters' => wfMessage( 'wikibase-faceted-search-filters' )->text(),
				'msg-and' => wfMessage( 'wikibase-faceted-search-and' )->text(),
				'msg-or' => wfMessage( 'wikibase-faceted-search-or' )->text(),
				'facets' => $this->facetsToViewModel()
			]
		);
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	private function facetsToViewModel( /* TODO: parameters */ ): array {
		// TODO: Derive label from propertyId
		return [
			[
				'label' => 'Author',
				'propertyId' => 'P100',
				'type' => FacetType::LIST->value,
				'values-html' => $this->getItemsHtml(
					$this->getExampleListItems(),
					FacetType::LIST,
					'P100'
				),
				'expanded' => true,
				'hasToggle' => true
			],
			[
				'label' => 'Year',
				'propertyId' => 'P200',
				'type' => FacetType::RANGE->value,
				'values-html' => $this->getItemsHtml(
					[ $this->getExampleRangeItems()[0] ],
					FacetType::RANGE,
					'P200'
				),
				'expanded' => true,
				'hasToggle' => false
			],
			[
				'label' => 'Pages',
				'propertyId' => 'P300',
				'type' => FacetType::RANGE->value,
				'values-html' => $this->getItemsHtml(
					[ $this->getExampleRangeItems()[1] ],
					FacetType::RANGE,
					'P300'
				),
				'expanded' => false,
				'hasToggle' => false
			]
		];
	}

	/**
	 * @return array<array{label: string, count: int}>
	 */
	private function getExampleListItems(): array {
		// TODO: Derive label from itemId
		return [
			[
				'label' => 'Alice', // TODO: lookup of label and URL for item-id (or property-id) typed values,
				'itemId' => 'Q100',
				'count' => 42
			],
			[
				'label' => 'Bob',
				'itemId' => 'Q200',
				'count' => 23
			],
			[
				'label' => 'Charlie',
				'itemId' => 'Q300',
				'count' => 17
			],
			[
				'label' => 'David',
				'itemId' => 'Q400',
				'count' => 9
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
	private function getItemsHtml( array $items, FacetType $type, string $propertyId ): string {
		if ( $items === [] ) {
			return '';
		}

		$hasConstraints = array_key_exists( $propertyId, $this->constraints );

		$html = '';

		foreach ( $items as $i => $item ) {
			$item['id'] = Sanitizer::escapeIdForAttribute( htmlspecialchars( "$propertyId-$i" ) );

			// $item['itemId'] is always a string but PHPStan does not know that
			if ( $type === FacetType::LIST && is_string( $item['itemId'] ) ) {
				$item['selected'] = $hasConstraints ? $this->getFacetItemState( $propertyId, $item['itemId'] ) : false;
				$item['url'] = $this->getFacetItemUrl( $propertyId, $item['itemId'] );
			}

			try {
				$html .= $this->parser->processTemplate(
					$this->facetTypeToTemplateName( $type ),
					$item
				);
			} catch ( RuntimeException ) {
				// ignore
			}
		}

		return $html;
	}

	private function facetTypeToTemplateName( FacetType $type ): string {
		return match ( $type ) {
			FacetType::LIST => 'FacetItemCheckbox',
			FacetType::RANGE => 'FacetItemRange',
		};
	}

	private function getFacetItemState( string $propertyId, string $itemId ): bool {
		// TODO: Persist expanded state
		// TODO: Handles other constraint values
		return in_array( $itemId, $this->constraints[$propertyId]->getAndSelectedValues() );
	}

	private function getFacetItemUrl( string $propertyId, string $itemId ): string {
		// TODO: Support negated value
		$facetType = 'haswbfacet';
		// TODO: Support OR facet
		$facetQuery = "$facetType:$propertyId=$itemId";

		return $this->searchUrlBuilder->buildUrl( $facetQuery );
	}

}
