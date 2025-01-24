<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Html\TemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\LocalizedTextLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use Wikibase\DataModel\Entity\ItemId;

class UiBuilder {

	public function __construct(
		private readonly Config $config,
		private readonly FacetHtmlBuilder $facetHtmlBuilder,
    private readonly ItemTypeLabelLookup $itemTypeLabelLookup,
		private readonly LocalizedTextLookup $localizedTextLookup,
		private readonly TemplateParser $templateParser,
		private readonly QueryStringParser $queryStringParser,
	) {
	}

	public function createHtml( string $searchQuery ): string {
		$query = $this->parseQuery( $searchQuery );
		$itemType = $query->getItemTypes()[0] ?? null;

		return $this->renderTemplate(
			$this->buildTabsViewModel(
				selectedItemType: $itemType
			),
			$this->buildFacetsViewModel(
				itemType: $itemType,
				query: $query
			)
		);
	}

	private function renderTemplate( array $instancesViewModel, array $facetsViewModel ): string {
		return $this->templateParser->processTemplate(
			'Layout',
			[
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

	private function buildFacetsViewModel( ?ItemId $itemType, Query $query ): array {
		if ( $itemType === null ) {
			return [];
		}

		$facets = [];

		foreach ( $this->config->getFacetConfigForItemType( $itemType ) as $facetConfig ) {
			$facets[] = $this->buildFacetViewModel(
				$facetConfig,
				$query->getConstraintsForProperty( $facetConfig->propertyId ) ?? new PropertyConstraints( $facetConfig->propertyId )
			);
		}

		return $facets;
	}

	private function buildFacetViewModel( FacetConfig $facet, PropertyConstraints $state ): array {
		return [
			'label' => $this->localizedTextLookup->getLabelFromEntityId( $config->propertyId ),
			'propertyId' => $config->propertyId->getSerialization(),
			'type' => $config->type->value, // TODO: is this needed?
			'expanded' => true, // TODO: get this from the URL somehow
			'facetHtml' => $this->facetHtmlBuilder->buildHtml( $facet, $state )
		];
	}

}
