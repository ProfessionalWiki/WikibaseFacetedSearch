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
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\LabelLookup;

class SidebarHtmlBuilder {

	public function __construct(
		private readonly Config $config,
		private readonly FacetHtmlBuilder $facetHtmlBuilder,
		private readonly LabelLookup $labelLookup,
		private readonly TemplateParser $templateParser,
		private readonly QueryStringParser $queryStringParser,
	) {
	}

	public function createHtml( string $searchQuery ): string {
		$query = $this->parseQuery( $searchQuery );
		$itemType = $query->getItemTypes()[0] ?? null;

		return $this->renderTemplate(
			$this->buildFacetsViewModel(
				itemType: $itemType,
				query: $query
			)
		);
	}

	private function renderTemplate( array $facetsViewModel ): string {
		if ( count( $facetsViewModel ) === 0 ) {
			return '';
		}

		return $this->templateParser->processTemplate(
			'Sidebar',
			[
				'instanceId' => $this->config->getItemTypeProperty()->getSerialization(),
				'facets' => $facetsViewModel,
				'msg-filters' => wfMessage( 'wikibase-faceted-search-filters' )->text(),
			]
		);
	}

	private function parseQuery( string $searchQuery ): Query {
		return $this->queryStringParser->parse( $searchQuery );
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
		$facetHtml = $this->facetHtmlBuilder->buildHtml( $facet, $state );

		return [
			'label' => $this->getFacetLabel( $facet->propertyId ),
			'propertyId' => $facet->propertyId->getSerialization(),
			'type' => $facet->type->value, // TODO: is this needed?
			'expanded' => true, // TODO: get this from the URL somehow
			'facetHtml' => $facetHtml,
			'showFacet' => $facetHtml !== '',
		];
	}

	private function getFacetLabel( PropertyId $propertyId ): string {
		return $this->labelLookup->getLabel( $propertyId )?->getText() ?? $propertyId->getSerialization();
	}

}
