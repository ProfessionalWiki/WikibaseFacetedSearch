<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query;

use CirrusSearch\Query\SimpleKeywordFeature;
use CirrusSearch\Search\SearchContext;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use Wikibase\DataModel\Entity\ItemId;

class HasWbFacetFeature extends SimpleKeywordFeature {

	public function __construct(
		private readonly Config $config,
		private readonly QueryStringParser $queryStringParser,
		private readonly ItemTypeQueryBuilder $itemTypeQueryBuilder,
		private readonly FacetQueryBuilder $facetQueryBuilder
	) {
	}

	/**
	 * @return string[]
	 */
	protected function getKeywords(): array {
		return [ 'haswbfacet' ];
	}

	protected function doApply( SearchContext $context, $key, $value, $quotedValue, $negated ): array {
		$itemTypes = $this->getItemTypes( $context->getOriginalSearchTerm() );

		if ( $itemTypes === [] ) {
			return [ null, false ];
		}

		$constraints = $this->getConstraintsForValue( $value );

		if ( $constraints === null ) {
			return [ $this->itemTypeQueryBuilder->buildQuery( $itemTypes ), false ];
		}

		foreach ( $itemTypes as $itemTypeId ) {
			$facet = $this->config->getConfigForProperty( $itemTypeId, $constraints->propertyId );

			if ( $facet === null ) {
				continue;
			}

			return [ $this->facetQueryBuilder->buildQuery( $facet, $constraints ), false ];
		}

		return [ null, false ];
	}

	/**
	 * @return ItemId[]
	 */
	private function getItemTypes( string $originalQueryString ): array {
		return $this->queryStringParser->parse( $originalQueryString )->getItemTypes();
	}

	/**
	 * @param string $value Keyword value for a single facet
	 */
	private function getConstraintsForValue( string $value ): ?PropertyConstraints {
		$constraints = $this->queryStringParser->parse( "haswbfacet:$value" )->getConstraintsPerProperty();

		return $constraints === [] ? null : reset( $constraints );
	}

}
