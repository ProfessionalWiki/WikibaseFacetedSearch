<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Terms;

class ElasticQueryFilter {

	public function removeOrFacets( AbstractQuery $currentQuery, Query $parsedQuery ): AbstractQuery {
		if ( $parsedQuery->getItemTypes() === [] ) {
			return $currentQuery;
		}

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

		$orFieldNames = $this->getOrFieldNames( $this->getOrConstraints( $parsedQuery ) );

		$newQueryFilter = new BoolQuery();
		$newQueryFilter->setParam( 'must', $this->getNonOrQueryConditions( $currentQueryConditions, $orFieldNames ) );

		$newQuery = new BoolQuery();
		$newQuery->setParams( $currentQuery->getParams() );
		$newQuery->setParam( 'filter', $newQueryFilter );

		return $newQuery;
	}

	private function getOrConstraints( Query $parsedQuery ): array {
		return array_filter(
			$parsedQuery->getConstraintsPerProperty(),
			fn( PropertyConstraints $constraints ) => $constraints->getOrSelectedValues() !== []
		);
	}

	/**
	 * @param PropertyConstraints[] $orConstraints
	 * @return string[]
	 */
	private function getOrFieldNames( array $orConstraints ): array {
		return array_map(
			fn( PropertyConstraints $constraints ) => 'wbfs_' . $constraints->propertyId->getSerialization(),
			$orConstraints
		);
	}

	/**
	 * @param AbstractQuery[] $conditions
	 * @param string[] $orFieldNames
	 * @return AbstractQuery[]
	 */
	private function getNonOrQueryConditions( array $conditions, array $orFieldNames ): array {
		return array_values(
			array_filter(
				$conditions,
				fn( AbstractQuery $condition ) => !$this->isOrCondition( $condition, $orFieldNames )
			)
		);
	}

	/**
	 * @param string[] $orFieldNames
	 */
	private function isOrCondition( AbstractQuery $condition, array $orFieldNames ): bool {
		if ( !( $condition instanceof Terms ) ) {
			return false;
		}

		// An "or" Term query has the field name as the key in the params array.
		return count(
			array_intersect_key( $condition->getParams(), array_flip( $orFieldNames ) )
		) > 0;
	}

}
