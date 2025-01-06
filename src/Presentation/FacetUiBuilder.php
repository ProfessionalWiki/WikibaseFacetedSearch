<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use InvalidArgumentException;
use MediaWiki\Html\TemplateParser;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Sanitizer;
use MediaWiki\Specials\SpecialSearch;
use MediaWiki\Utils\UrlUtils;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use RuntimeException;
use Wikibase\DataModel\Entity\ItemId;

class FacetUiBuilder {

	/** @var array<string, string> */
	private array $facetTemplates = [];

	/** @var array<string, string> */
	private array $query = [];

	public function __construct(
		private readonly TemplateParser $parser,
		private readonly Config $config, // TODO: use
		private readonly SpecialSearch $specialSearch,
		private readonly UrlUtils $urlUtils
	) {
		// TODO: Perhaps we should do a map for template name and FacetType
		// TODO: Should this go into FacetType?
		$this->facetTemplates = [
			FacetType::LIST->value => 'Checkbox',
			FacetType::RANGE->value => 'Range'
		];
	}

	// TODO: parameter or constructor argument: values and counts (from https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/23)
	// TODO: parameter: selected values (from QueryStringParser https://github.com/ProfessionalWiki/WikibaseFacetedSearch/issues/31)
	public function createHtml( ItemId $itemType ): string {
		$this->config->getFacetConfigForInstanceType( $itemType );
		$this->query = $this->getSearchQueryFromUrl();

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
	 * @return array<string, string>
	 */
	private function getSearchQueryFromUrl(): array {
		$parts = $this->urlUtils->parse( $this->specialSearch->getRequest()->getFullRequestURL() );
		return wfCgiToArray( $parts['query'] ?? '' );
	}

	private function getFacetItemState( string $facetName, string $itemValue ): bool {
		if ( !array_key_exists( "wbfs-$facetName", $this->query ) ) {
			return false;
		}

		return in_array( $itemValue, explode( ',', $this->query[ "wbfs-$facetName" ] ) );
	}

	// TODO: Find a better identifier for facetName
	// TODO: Encode facetName and itemValue
	private function getFacetItemUrl( string $facetName, string $itemValue, bool $selected ): string {
		// TODO: Sync query parameter name with search query
		$queryKey = "wbfs-$facetName";
		$query = $this->query;

		if ( !array_key_exists( $queryKey, $query ) ) {
			$query[$queryKey] = $itemValue;
		} else {
			if ( $selected === false ) {
				$query[$queryKey] = "$query[$queryKey],$itemValue";
			} else {
				$queryValues = explode( ',', $query[$queryKey] );
				$queryValues = array_filter( $queryValues, fn( $value ) => $value !== $itemValue );
				if ( empty( $queryValues ) ) {
					unset( $query[$queryKey] );
				} else {
					$query[$queryKey] = implode( ',', $queryValues );
				}
			}
		}

		$parts['query'] = wfArrayToCgi( $query );
		return UrlUtils::assemble( $parts );
	}

	/**
	 * @return array<array{label: string, count: int}>
	 */
	private function getExampleListItems(): array {
		return [
			[
				'label' => 'Alice', // TODO: lookup of label and URL for item-id (or property-id) typed values
				'count' => 42
			],
			[
				'label' => 'Bob',
				'count' => 23
			],
			[
				'label' => 'Charlie',
				'count' => 17
			],
			[
				'label' => 'David',
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

			if ( $type === FacetType::LIST->value ) {
				$item['selected'] = $this->getFacetItemState( $facetName, $item['label'] );
				$item['url'] = $this->getFacetItemUrl( $facetName, $item['label'], $item['selected'] );
			}

			try {
				$html .= $this->parser->processTemplate( $template, $item );
			} catch ( RuntimeException ) {
				// ignore
			}
		}

		return $html;
	}

}
