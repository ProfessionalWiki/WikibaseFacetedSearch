<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCount;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounter;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\LabelLookup;

/**
 * Builds the facets of a search query as plain arrays, for the API response.
 *
 * @phpstan-type FacetValue array{value: string|float|int, count: int, label: string}
 * @phpstan-type Facet array{property: string, label: string, type: string, values: list<FacetValue>}
 */
class FacetsResponseBuilder {

	public function __construct(
		private readonly Config $config,
		private readonly LabelLookup $labelLookup,
		private readonly ValueCounter $valueCounter,
		private readonly FacetValueFormatter $valueFormatter
	) {
	}

	/**
	 * The facets configured for the first item type of the query, in configuration order.
	 *
	 * @return list<Facet>
	 */
	public function buildFacets( Query $query ): array {
		$itemType = $query->getItemTypes()[0] ?? null;

		if ( $itemType === null ) {
			return [];
		}

		$facets = [];

		foreach ( $this->config->getFacetConfigForItemType( $itemType ) as $facetConfig ) {
			$facets[] = $this->buildFacet(
				$facetConfig,
				$query->constraints->getOrCreateConstraints( $facetConfig->propertyId )
			);
		}

		return $facets;
	}

	/**
	 * @return Facet
	 */
	private function buildFacet( FacetConfig $facet, PropertyConstraints $constraints ): array {
		return [
			'property' => $facet->propertyId->getSerialization(),
			'label' => $this->getFacetLabel( $facet->propertyId ),
			'type' => $facet->type->value,
			'values' => $this->buildValues( $facet, $constraints )
		];
	}

	private function getFacetLabel( PropertyId $propertyId ): string {
		return $this->labelLookup->getLabel( $propertyId )?->getText() ?? $propertyId->getSerialization();
	}

	/**
	 * @return list<FacetValue>
	 */
	private function buildValues( FacetConfig $facet, PropertyConstraints $constraints ): array {
		if ( $facet->type !== FacetType::LIST ) {
			return [];
		}

		$values = [];

		foreach ( $this->valueCounter->countValues( $constraints )->asArray() as $valueCount ) {
			$values[] = $this->buildValue( $valueCount, $facet->propertyId );
		}

		return $values;
	}

	/**
	 * @return FacetValue
	 */
	private function buildValue( ValueCount $valueCount, PropertyId $propertyId ): array {
		return [
			'value' => $valueCount->value,
			'count' => $valueCount->count,
			'label' => $this->valueFormatter->getLabel( (string)$valueCount->value, $propertyId )
		];
	}

}
