<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence\Search\Query;

use Elastica\Query\AbstractQuery;
use Elastica\Query\Range;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\RangeFacetQueryBuilder;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Services\Lookup\InMemoryDataTypeLookup;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\RangeFacetQueryBuilder
 */
class RangeFacetQueryBuilderTest extends TestCase {

	private const QUANTITY_PROPERTY = 'P100';
	private const STRING_PROPERTY = 'P200';
	private const TIME_PROPERTY = 'P300';
	private const ITEM_PROPERTY = 'P400';

	public function testBuildsQueryForQuantityRange(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newRangeFacetConfig( self::QUANTITY_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::QUANTITY_PROPERTY ),
				min: 42,
				max: 9001
			)
		);

		$this->assertIsRangeQueryWithParams(
			self::QUANTITY_PROPERTY,
			[ 'gte' => 42.0, 'lte' => 9001.0 ],
			$query
		);
	}

	private function newFacetQueryBuilder(): RangeFacetQueryBuilder {
		return new RangeFacetQueryBuilder(
			$this->newDataTypeLookup()
		);
	}

	private function newDataTypeLookup(): InMemoryDataTypeLookup {
		$dataTypeLookup = new InMemoryDataTypeLookup();

		$types = [
			self::QUANTITY_PROPERTY => 'quantity',
			self::STRING_PROPERTY => 'string',
			self::TIME_PROPERTY => 'time',
			self::ITEM_PROPERTY => 'wikibase-item'
		];

		foreach ( $types as $pId => $type ) {
			$dataTypeLookup->setDataTypeForProperty(
				new NumericPropertyId( $pId ),
				$type
			);
		}

		return $dataTypeLookup;
	}

	private function newRangeFacetConfig( string $propertyId ): FacetConfig {
		return new FacetConfig(
			new ItemId( 'Q404' ),
			new NumericPropertyId( $propertyId ),
			FacetType::RANGE
		);
	}

	private function assertIsRangeQueryWithParams( string $propertyId, array $values, AbstractQuery $query ): void {
		$this->assertInstanceOf( Range::class, $query );
		$this->assertSame(
			[
				'wbfs_' . $propertyId => $values
			],
			$query->getParams()
		);
	}

	public function testBuildsQueryForQuantityRangeWithOnlyMinimum(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newRangeFacetConfig( self::QUANTITY_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::QUANTITY_PROPERTY ),
				min: 42
			)
		);

		$this->assertIsRangeQueryWithParams(
			self::QUANTITY_PROPERTY,
			[ 'gte' => 42.0, 'lte' => null ],
			$query
		);
	}

	public function testBuildsQueryForQuantityRangeWithOnlyMaximum(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newRangeFacetConfig( self::QUANTITY_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::QUANTITY_PROPERTY ),
				max: 9001
			)
		);

		$this->assertIsRangeQueryWithParams(
			self::QUANTITY_PROPERTY,
			[ 'gte' => null, 'lte' => 9001.0 ],
			$query
		);
	}

	public function testBuildsQueryForDateRange(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newRangeFacetConfig( self::TIME_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::TIME_PROPERTY ),
				min: 2000,
				max: 2010
			)
		);

		$this->assertEquals(
			new Range(
				'wbfs_' . self::TIME_PROPERTY,
				[ 'gte' => '2000-01-01', 'lte' => '2010-12-31' ]
			),
			$query
		);
	}

	public function testBuildsQueryForDateRangeWithOnlyMinimum(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newRangeFacetConfig( self::TIME_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::TIME_PROPERTY ),
				min: 2000
			)
		);

		$this->assertEquals(
			new Range(
				'wbfs_' . self::TIME_PROPERTY,
				[ 'gte' => '2000-01-01', 'lte' => null ]
			),
			$query
		);
	}

	public function testBuildsQueryForDateRangeWithOnlyMaximum(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newRangeFacetConfig( self::TIME_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::TIME_PROPERTY ),
				max: 2010
			)
		);

		$this->assertEquals(
			new Range(
				'wbfs_' . self::TIME_PROPERTY,
				[ 'gte' => null, 'lte' => '2010-12-31' ]
			),
			$query
		);
	}

	public function testDoesNotBuildQueryForStringRange(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newRangeFacetConfig( self::STRING_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::STRING_PROPERTY ),
				orSelectedValues: [ '2000', '2010' ]
			)
		);

		$this->assertNull( $query );
	}

	public function testDoesNotBuildQueryForItemRange(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newRangeFacetConfig( self::ITEM_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::ITEM_PROPERTY ),
				orSelectedValues: [ 'Q100', 'Q200' ]
			)
		);

		$this->assertNull( $query );
	}

}
