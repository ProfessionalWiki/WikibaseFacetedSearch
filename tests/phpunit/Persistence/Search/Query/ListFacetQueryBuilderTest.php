<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence\Search\Query;

use Elastica\Query\AbstractQuery;
use Elastica\Query\Exists;
use Elastica\Query\Terms;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\ListFacetQueryBuilder;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Services\Lookup\InMemoryDataTypeLookup;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\ListFacetQueryBuilder
 */
class ListFacetQueryBuilderTest extends TestCase {

	private const QUANTITY_PROPERTY = 'P100';
	private const STRING_PROPERTY = 'P200';
	private const TIME_PROPERTY = 'P300';
	private const ITEM_PROPERTY = 'P400';

	public function testBuildsQueryForStringList(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newListFacetConfig( self::STRING_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::STRING_PROPERTY ),
				orSelectedValues: [ 'foo', 'bar' ]
			)
		);

		$this->assertIsTermsQueryWithParams(
			self::STRING_PROPERTY,
			[ 'foo', 'bar' ],
			$query
		);
	}

	private function newFacetQueryBuilder(): ListFacetQueryBuilder {
		return new ListFacetQueryBuilder(
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

	private function newListFacetConfig( string $propertyId ): FacetConfig {
		return new FacetConfig(
			new ItemId( 'Q404' ),
			new NumericPropertyId( $propertyId ),
			FacetType::LIST
		);
	}

	private function assertIsTermsQueryWithParams( string $propertyId, array $values, AbstractQuery $query ): void {
		$this->assertInstanceOf( Terms::class, $query );
		$this->assertSame(
			[
				'wbfs_' . $propertyId => $values
			],
			$query->getParams()
		);
	}

	public function testBuildsQueryForItemList(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newListFacetConfig( self::ITEM_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::ITEM_PROPERTY ),
				orSelectedValues: [ 'Q100', 'Q200' ]
			)
		);

		$this->assertIsTermsQueryWithParams(
			self::ITEM_PROPERTY,
			[ 'Q100', 'Q200' ],
			$query
		);
	}

	public function testBuildsQueryForQuantityList(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newListFacetConfig( self::QUANTITY_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::QUANTITY_PROPERTY ),
				orSelectedValues: [ '42', '100.50', '9001' ]
			)
		);

		$this->assertIsTermsQueryWithParams(
			self::QUANTITY_PROPERTY,
			[ 42.0, 100.5, 9001.0 ],
			$query
		);
	}

	public function testDBuildsQueryForDateList(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newListFacetConfig( self::TIME_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::TIME_PROPERTY ),
				orSelectedValues: [ '-304905600000', '315532800000', '946684800000', '1292112000000' ]
			)
		);

		$this->assertIsTermsQueryWithParams(
			self::TIME_PROPERTY,
			[ '1960-05-04T00:00:00Z', '1980-01-01T00:00:00Z', '2000-01-01T00:00:00Z', '2010-12-12T00:00:00Z' ],
			$query
		);
	}

	public function testBuildsQueryForStringListWithSingleAndValue(): void {
		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newListFacetConfig( self::STRING_PROPERTY ),
			new PropertyConstraints(
				new NumericPropertyId( self::STRING_PROPERTY ),
				andSelectedValues: [ 'foo' ]
			)
		);

		$this->assertIsTermsQueryWithParams(
			self::STRING_PROPERTY,
			[ 'foo' ],
			$query
		);
	}

	public function testBuildsAnyValueQuery(): void {
		$constraints = ( new PropertyConstraints(
			new NumericPropertyId( self::STRING_PROPERTY )
		) )->requireAnyValue();

		$query = $this->newFacetQueryBuilder()->buildQuery(
			$this->newListFacetConfig( self::STRING_PROPERTY ),
			$constraints
		);

		$this->assertInstanceOf( Exists::class, $query );
		$this->assertSame(
			[
				'field' => 'wbfs_' . self::STRING_PROPERTY
			],
			$query->getParams()
		);
	}

}
