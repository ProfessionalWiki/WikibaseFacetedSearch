<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use Elastica\Query\AbstractQuery;
use MediaWiki\Html\TemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\LabelLookup;

class UiBuilder {

	public function __construct(
		private readonly Config $config,
		private readonly FacetHtmlBuilder $facetHtmlBuilder,
		private readonly LabelLookup $labelLookup,
		private readonly ItemTypeLabelLookup $itemTypeLabelLookup,
		private readonly TemplateParser $templateParser,
		private readonly QueryStringParser $queryStringParser,
	) {
	}

	public function createHtml( string $searchQuery, AbstractQuery $currentQuery ): string {
		$query = $this->parseQuery( $searchQuery );
		$itemType = $query->getItemTypes()[0] ?? null;

		return $this->renderTemplate(
			$this->buildTabsViewModel(
				selectedItemType: $itemType
			),
			$this->buildFacetsViewModel(
				itemType: $itemType,
				query: $query,
				currentQuery: $currentQuery
			)
		);
	}

	private function renderTemplate( array $instancesViewModel, array $facetsViewModel ): string {
		return $this->templateParser->processTemplate(
			'Layout',
			[
				'showSidebar' => count( $facetsViewModel ) > 0,
				'instanceId' => $this->config->getItemTypeProperty()->getSerialization(),
				'instances' => $instancesViewModel,
				'facets' => $facetsViewModel,
				'msg-filters' => wfMessage( 'wikibase-faceted-search-filters' )->text(),
			]
		);
	}

	private function parseQuery( string $searchQuery ): Query {
		return $this->queryStringParser->parse( $searchQuery );
	}

	private function buildTabsViewModel( ?ItemId $selectedItemType ): array {
		$tabs = [];

		foreach ( $this->config->getItemTypes() as $itemType ) {
			$tabs[] = [
				'label' => $this->itemTypeLabelLookup->getLabel( $itemType ),
				'value' => $itemType->getSerialization(),
				'selected' => $itemType->equals( $selectedItemType )
			];
		}

		return [
			[
				'label' => wfMessage( 'wikibase-faceted-search-instance-type-all' )->text(),
				'value' => '',
				'selected' => $this->noTabsAreSelected( $tabs )
			],
			...$tabs
		];
	}

	/**
	 * @param array<array{selected: bool}> $tabs
	 */
	private function noTabsAreSelected( array $tabs ): bool {
		return !array_reduce( $tabs, ( fn( $carry, $tab ) => $carry || $tab['selected'] ), false );
	}

	private function buildFacetsViewModel( ?ItemId $itemType, Query $query, AbstractQuery $currentQuery ): array {
		if ( $itemType === null ) {
			return [];
		}

		$facets = [];

		foreach ( $this->config->getFacetConfigForItemType( $itemType ) as $facetConfig ) {
			$facets[] = $this->buildFacetViewModel(
				$facetConfig,
				$query->getConstraintsForProperty( $facetConfig->propertyId ) ?? new PropertyConstraints( $facetConfig->propertyId ),
				$currentQuery
			);
		}

		return $facets;
	}

	private function buildFacetViewModel( FacetConfig $facet, PropertyConstraints $state, AbstractQuery $query ): array {
		return [
			'label' => $this->getPropertyLabel( $facet->propertyId ),
			'propertyId' => $facet->propertyId->getSerialization(),
			'type' => $facet->type->value, // TODO: is this needed?
			'expanded' => true, // TODO: get this from the URL somehow
			'facetHtml' => $this->facetHtmlBuilder->buildHtml( $facet, $state, $query )
		];
	}

	private function getPropertyLabel( PropertyId $id ): string {
		return $this->labelLookup->getLabel( $id )?->getText() ?? $id->getSerialization();
	}

}
