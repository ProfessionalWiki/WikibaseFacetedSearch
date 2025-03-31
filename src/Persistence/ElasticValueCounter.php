<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use Elastica\Query\AbstractQuery;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ElasticQueryFilter;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCount;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounter;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounts;
use Wikibase\DataModel\Entity\PropertyId;

class ElasticValueCounter implements ValueCounter {

	public function __construct(
		private readonly ElasticQueryRunner $queryRunner,
		private readonly AbstractQuery $currentQuery,
		private readonly ElasticQueryFilter $queryFilter,
	) {
	}

	/**
	 * Count the values for a given property, highest occurrences first.
	 * Values are indexed per property at wbfs_P123, where P123 is the serialization of the property id.
	 */
	public function countValues( PropertyConstraints $constraints ): ValueCounts {
		$query = [
			'size' => 0,
			'query' => $this->getFilteredQuery( $constraints )->toArray(),
			'aggs' => [
				'valueCounts' => [
					'terms' => [
						'field' => 'wbfs_' . $constraints->propertyId->getSerialization(),
						'size' => 100,
					],
				],
			],
		];

		$resultSet = $this->queryRunner->runQuery( $query )->getData();

		/**
		 * @var ValueCount[] $valueCounts
		 */
		$valueCounts = [];

		if ( isset( $resultSet['aggregations']['valueCounts']['buckets'] ) ) {
			foreach ( $resultSet['aggregations']['valueCounts']['buckets'] as $bucket ) {
				$valueCounts[] = new ValueCount(
					$bucket['key'],
					(int)$bucket['doc_count']
				);
			}
		}

		return new ValueCounts( $valueCounts );
	}

	private function getFilteredQuery( PropertyConstraints $constraints ): AbstractQuery {
		if ( $constraints->hasNoValue() || $constraints->getOrSelectedValues() !== [] ) {
			return $this->queryFilter->removeFacet( $this->currentQuery, $constraints->propertyId );
		}
		return $this->currentQuery;
	}

}
