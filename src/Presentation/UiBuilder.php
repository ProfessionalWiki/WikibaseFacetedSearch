<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Html\TemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use Wikibase\DataModel\Entity\PropertyId;
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
		$query = $this->parseQuery( $searchQuery );
		$instanceItemId = $query->getInstanceItemId();

		return $this->renderTemplate(
			$this->buildInstancesViewModel(
				instancePropertyId: $query->getInstancePropertyId(),
				instanceItemId: $instanceItemId
			),
			$this->buildFacetsViewModel(
				instanceItemId: $instanceItemId,
				query: $query
			)
		);
	}

	private function renderTemplate( array $instancesViewModel, array $facetsViewModel ): string {
		return $this->templateParser->processTemplate(
			'Layout',
			[
				'instanceId' => 'P1460', // TODO: Link to config
				'instances' => $instancesViewModel,
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
	private function buildInstancesViewModel( ?PropertyId $instancePropertyId, ?ItemId $instanceItemId ): array {
		$instances = [
			[
				'label' => wfMessage( 'wikibase-faceted-search-instance-type-all' )->text(),
				'value' => ''
			]
		];

		// TODO: Get instances from config
		$instancesExample = [
			[
				'label' => 'People',
				'value' => 'Q5976445'
			],
			[
				'label' => 'Documents',
				'value' => 'Q5976449'
			]
		];

		$instances = array_merge( $instances, $instancesExample );

		$instanceItemIdStr = $instanceItemId ? $instanceItemId->getSerialization() : '';
		$instances = array_map( function( array $instance ) use ( $instanceItemIdStr )	 {
			$instance['selected'] = $instance['value'] === $instanceItemIdStr ? 'true' : 'false';
			return $instance;
		}, $instances );

		// TODO: Get currently selected instance from query
		return $instances;
	}

	private function buildFacetsViewModel( ?ItemId $instanceItemId, Query $query ): array {
		if ( $instanceItemId === null ) {
			return [];
		}

		$facets = [];

		foreach ( $this->config->getFacetConfigForInstanceType( $instanceItemId ) as $facetConfig ) {
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
