<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Terms;
use Wikibase\DataModel\Entity\PropertyId;

class ElasticQueryFilter {

	public function removeFacet( AbstractQuery $currentQuery, PropertyId $propertyId ): AbstractQuery {
		if ( !$currentQuery->hasParam( 'filter' ) ) {
			return $currentQuery;
		}

		/** @var AbstractQuery[] $currentQueryFilter */
		$currentQueryFilter = $currentQuery->getParam( 'filter' );

		if ( $currentQueryFilter === [] ) {
			return $currentQuery;
		}

		/** @var AbstractQuery[] $currentQueryConditions */
		$currentQueryConditions = $currentQueryFilter[0]->getParam( 'must' );

		$newQueryFilter = new BoolQuery();
		$newQueryFilter->setParam( 'must', $this->getFilteredConditions( $currentQueryConditions, $propertyId ) );

		$newQuery = new BoolQuery();
		$newQuery->setParams( $currentQuery->getParams() );
		$newQuery->setParam( 'filter', $newQueryFilter );

		return $newQuery;
	}

	/**
	 * @param AbstractQuery[] $conditions
	 * @return AbstractQuery[]
	 */
	private function getFilteredConditions( array $conditions, PropertyId $propertyId ): array {
		return array_values(
			array_filter(
				$conditions,
				fn( AbstractQuery $condition ) => !$this->shouldRemoveCondition( $condition, $propertyId )
			)
		);
	}

	private function shouldRemoveCondition( AbstractQuery $condition, PropertyId $propertyId ): bool {
		return $condition instanceof Terms
			&& array_key_exists( 'wbfs_' . $propertyId->getSerialization(), $condition->getParams() );
	}

}
