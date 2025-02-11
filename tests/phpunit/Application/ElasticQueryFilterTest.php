<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use Elastica\Query\BoolQuery;
use Elastica\Query\MatchAll;
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

	public function testRemovesOrQueriesInSearchWithOrConditions(): void {
		$filter = new BoolQuery();
		$filter->addMust( new Terms( 'wbfs_P42', [ 'Q1' ] ) );
		$filter->addMust( new Terms( 'wbfs_P1337', [ 'Q100', 'Q200' ] ) );
		$filter->addMust( new Terms( 'wbfs_P9000', [ 'Q500', 'Q600' ] ) );
		$filter->addMust( new Terms( 'namespace', [ 0, 120 ] ) );

		$query = new BoolQuery();
		$query->addMust( new MatchAll() );
		$query->addFilter( $filter );

		$newQuery = $this->newElasticQueryFilter()->removeOrFacets(
			currentQuery: $query,
			parsedQuery: new Query(
				constraints: new PropertyConstraintsList(
					new PropertyConstraints(
						propertyId: new NumericPropertyId( 'P1337' ),
						orSelectedValues: [ new ItemId( 'Q100' ), new ItemId( 'Q200' ) ]
					),
					new PropertyConstraints(
						propertyId: new NumericPropertyId( 'P9000' ),
						orSelectedValues: [ new ItemId( 'Q500' ), new ItemId( 'Q600' ) ]
					)
				),
				itemTypes: [ new ItemId( 'Q1' ) ]
			)
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

	public function testRemovesNothingInSearchWithAndConditions(): void {
		$filter = new BoolQuery();
		$filter->addMust( new Terms( 'wbfs_P42', [ 'Q1' ] ) );
		$filter->addMust( new Terms( 'wbfs_P1337', [ 'Q100', 'Q200' ] ) );
		$filter->addMust( new Terms( 'wbfs_P9000', [ 'Q500', 'Q600' ] ) );
		$filter->addMust( new Terms( 'namespace', [ 0, 120 ] ) );

		$query = new BoolQuery();
		$query->addMust( new MatchAll() );
		$query->addFilter( $filter );

		$newQuery = $this->newElasticQueryFilter()->removeOrFacets(
			currentQuery: $query,
			parsedQuery: new Query(
				constraints: new PropertyConstraintsList(
					new PropertyConstraints(
						propertyId: new NumericPropertyId( 'P1337' ),
						andSelectedValues: [ new ItemId( 'Q100' ), new ItemId( 'Q200' ) ]
					),
					new PropertyConstraints(
						propertyId: new NumericPropertyId( 'P9000' ),
						andSelectedValues: [ new ItemId( 'Q500' ), new ItemId( 'Q600' ) ]
					)
				),
				itemTypes: [ new ItemId( 'Q1' ) ]
			)
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

	public function testRemovesOrQueryInSearchWithOrAndAndConditions(): void {
		$filter = new BoolQuery();
		$filter->addMust( new Terms( 'wbfs_P42', [ 'Q1' ] ) );
		$filter->addMust( new Terms( 'wbfs_P1337', [ 'Q100', 'Q200' ] ) );
		$filter->addMust( new Terms( 'wbfs_P9000', [ 'Q500', 'Q600' ] ) );
		$filter->addMust( new Terms( 'namespace', [ 0, 120 ] ) );

		$query = new BoolQuery();
		$query->addMust( new MatchAll() );
		$query->addFilter( $filter );

		$newQuery = $this->newElasticQueryFilter()->removeOrFacets(
			currentQuery: $query,
			parsedQuery: new Query(
				constraints: new PropertyConstraintsList(
					new PropertyConstraints(
						propertyId: new NumericPropertyId( 'P1337' ),
						andSelectedValues: [ new ItemId( 'Q100' ), new ItemId( 'Q200' ) ]
					),
					new PropertyConstraints(
						propertyId: new NumericPropertyId( 'P9000' ),
						orSelectedValues: [ new ItemId( 'Q500' ), new ItemId( 'Q600' ) ]
					)
				),
				itemTypes: [ new ItemId( 'Q1' ) ]
			)
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
								'namespace' => [ 0, 120 ]
							]
						]
					]
				]
			],
			$newQuery->getParam( 'filter' )->toArray()
		);
	}

	public function testRemovesNothingInSearchWithoutConditions(): void {
		$filter = new BoolQuery();
		$filter->addMust( new Terms( 'wbfs_P42', [ 'Q1' ] ) );
		$filter->addMust( new Terms( 'namespace', [ 0, 120 ] ) );

		$query = new BoolQuery();
		$query->addMust( new MatchAll() );
		$query->addFilter( $filter );

		$newQuery = $this->newElasticQueryFilter()->removeOrFacets(
			currentQuery: $query,
			parsedQuery: new Query( new PropertyConstraintsList(), itemTypes: [ new ItemId( 'Q1' ) ] )
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
