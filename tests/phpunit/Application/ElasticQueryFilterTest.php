<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use Elastica\Query\BoolQuery;
use Elastica\Query\MatchAll;
use Elastica\Query\Range;
use Elastica\Query\Terms;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ElasticQueryFilter;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraintsList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\ElasticQueryFilter
 */
class ElasticQueryFilterTest extends TestCase {

	public function testRemovesFacetQueryIfItExists(): void {
		$filter = new BoolQuery();
		$filter->addMust( new Terms( 'wbfs_P42', [ 'Q1' ] ) );
		$filter->addMust( new Terms( 'wbfs_P1337', [ 'Q100', 'Q200' ] ) );
		$filter->addMust( new Terms( 'wbfs_P9000', [ 'Q500', 'Q600' ] ) );
		$filter->addMust( new Terms( 'namespace', [ 0, 120 ] ) );

		$query = new BoolQuery();
		$query->addMust( new MatchAll() );
		$query->addFilter( $filter );

		$newQuery = $this->newElasticQueryFilter()->removeFacet(
			currentQuery: $query,
			propertyId: new NumericPropertyId( 'P1337' )
		);

		$this->assertSame(
			[
				'bool' => [
					'must' => [
						[
							'terms' => [
								'wbfs_P42' => [ 'Q1' ]
							]
						],
						[
							'terms' => [
								'wbfs_P9000' => [ 'Q500', 'Q600' ]
							]
						],
						[
							'terms' => [
								'namespace' => [ 0, 120 ]
							]
						]
					]
				]
			],
			$newQuery->getParam( 'filter' )->toArray()
		);
	}

	private function newElasticQueryFilter(): ElasticQueryFilter {
		return new ElasticQueryFilter();
	}

	public function testRemovesNothingIfFacetQueryDoesNotExist(): void {
		$filter = new BoolQuery();
		$filter->addMust( new Terms( 'wbfs_P42', [ 'Q1' ] ) );
		$filter->addMust( new Terms( 'wbfs_P1337', [ 'Q100', 'Q200' ] ) );
		$filter->addMust( new Terms( 'wbfs_P9000', [ 'Q500', 'Q600' ] ) );
		$filter->addMust( new Terms( 'namespace', [ 0, 120 ] ) );

		$query = new BoolQuery();
		$query->addMust( new MatchAll() );
		$query->addFilter( $filter );

		$newQuery = $this->newElasticQueryFilter()->removeFacet(
			currentQuery: $query,
			propertyId: new NumericPropertyId( 'P404' )
		);

		$this->assertSame(
			[
				'bool' => [
					'must' => [
						[
							'terms' => [
								'wbfs_P42' => [ 'Q1' ]
							]
						],
						[
							'terms' => [
								'wbfs_P1337' => [ 'Q100', 'Q200' ]
							]
						],
						[
							'terms' => [
								'wbfs_P9000' => [ 'Q500', 'Q600' ]
							]
						],
						[
							'terms' => [
								'namespace' => [ 0, 120 ]
							]
						]
					]
				]
			],
			$newQuery->getParam( 'filter' )->toArray()
		);
	}

	public function testDoesNotRemoveRangeFacetQuery(): void {
		$filter = new BoolQuery();
		$filter->addMust( new Terms( 'wbfs_P42', [ 'Q1' ] ) );
		$filter->addMust( new Range( 'wbfs_P1337', [ 'gte' => 1000, 'lte' => 2000 ] ) );
		$filter->addMust( new Terms( 'namespace', [ 0, 120 ] ) );

		$query = new BoolQuery();
		$query->addMust( new MatchAll() );
		$query->addFilter( $filter );

		$newQuery = $this->newElasticQueryFilter()->removeFacet(
			currentQuery: $query,
			propertyId: new NumericPropertyId( 'P1337' )
		);

		$this->assertSame(
			[
				'bool' => [
					'must' => [
						[
							'terms' => [
								'wbfs_P42' => [ 'Q1' ]
							]
						],
						[
							'range' => [
								'wbfs_P1337' => [ 'gte' => 1000, 'lte' => 2000 ]
							]
						],
						[
							'terms' => [
								'namespace' => [ 0, 120 ]
							]
						]
					]
				]
			],
			$newQuery->getParam( 'filter' )->toArray()
		);
	}

}
