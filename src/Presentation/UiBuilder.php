<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Html\TemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use Wikibase\DataModel\Entity\ItemId;

class UiBuilder {

	public function __construct(
		private readonly Config $config,
		private readonly FacetHtmlBuilder $facetHtmlBuilder,
		private readonly TemplateParser $templateParser,
		private readonly QueryStringParser $queryStringParser,
	) {
	}

	/**
	 * TODO: add integration tests
	 */
	public function createHtml( string $searchQuery ): string {
		return $this->renderTemplate(
			$this->buildFacetsViewModel(
				itemType: new ItemId( 'Q5976449' ), // TODO: get from search string
				query: $this->parseQuery( $searchQuery )
			)
		);
	}

	private function renderTemplate( array $facetsViewModel ): string {
		return $this->templateParser->processTemplate(
			'Layout',
			[
				'instances' => $this->buildInstancesViewModel(),
				'facets' => $facetsViewModel,
				'msg-filters' => wfMessage( 'wikibase-faceted-search-filters' )->text(),
			]
		);
	}

	private function parseQuery( string $searchQuery ): Query {
		return $this->queryStringParser->parse( $searchQuery );
	}

	/**
	 * @return array<array<string, string>>
	 */
	private function buildInstancesViewModel(): array {
		// TODO: Get instances from config
		// TODO: Get currently selected instance from query
		return [
			[
				'label' => wfMessage( 'wikibase-faceted-search-instance-type-all' )->text(),
				'value' => '',
				'selected' => 'true'
			],
			[
				'label' => 'Memes',
				'value' => 'Q100',
				'selected' => 'false'
			],
			[
				'label' => 'Cat Pictures',
				'value' => 'Q200',
				'selected' => 'false'
			]
		];
	}

	private function buildFacetsViewModel( ItemId $itemType, Query $query ): array {
		$facets = [];

		foreach ( $this->config->getFacetConfigForInstanceType( $itemType ) as $facetConfig ) {
			$facets[] = $this->buildFacetViewModel(
				$facetConfig,
				$query->getConstraintsForProperty( $facetConfig->propertyId ) ?? new PropertyConstraints( $facetConfig->propertyId )
			);
		}

		return $facets;
	}

	private function buildFacetViewModel( FacetConfig $config, PropertyConstraints $state ): array {
		return [
			'label' => $config->propertyId->getSerialization(), // TODO: look up label, or leave this up to the frontend?
			'propertyId' => $config->propertyId->getSerialization(),
			'type' => $config->type->value, // TODO: is this needed?
			'expanded' => true, // TODO: get this from the URL somehow
			'facetHtml' => $this->facetHtmlBuilder->buildHtml( $config, $state )
		];
	}

}
