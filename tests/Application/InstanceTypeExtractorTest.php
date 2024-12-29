<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use DataValues\DataValue;
use DataValues\DecimalValue;
use DataValues\MonolingualTextValue;
use DataValues\QuantityValue;
use DataValues\StringValue;
use DataValues\TimeValue;
use DataValues\UnboundedQuantityValue;
use Elasticsearch\Endpoints\Cluster\State;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\InstanceTypeExtractor
 */
class InstanceTypeExtractorTest extends TestCase {

	private const INSTANCE_TYPE_ID = 'P42';

	public function testReturnsInstanceTypeIdWithSingleInstanceTypeStatement(): void {
		$statements = new StatementList(
			$this->newStatement( 'P100', new StringValue( 'Foo' ) ),
			$this->newInstanceTypeIdStatement( 'Q1' ),
			$this->newStatement( 'P200', new StringValue( 'Bar' ) )
		);

		$this->assertHasInstanceType( 'Q1', $statements );
	}

	private function newStatement( string $propertyId, DataValue $value ): Statement {
		return new Statement(
			new PropertyValueSnak(
				new NumericPropertyId( $propertyId ),
				$value
			)
		);
	}

	private function newInstanceTypeIdStatement( string $itemId ): Statement {
		return $this->newStatement( self::INSTANCE_TYPE_ID, new EntityIdValue( new ItemId( $itemId ) ) );
	}

	private function assertHasInstanceType( string $instanceTypeId, StatementList $statements ): void {
		$this->assertEquals(
			new ItemId( $instanceTypeId ),
			( $this->newExtractor() )->getInstanceTypeId( $statements )
		);
	}

	private function newExtractor(): InstanceTypeExtractor {
		return new InstanceTypeExtractor(
			new NumericPropertyId( self::INSTANCE_TYPE_ID )
		);
	}

	public function testReturnsFirstInstanceTypeIdWithMultipleInstanceTypeStatements(): void {
		$statements = new StatementList(
			$this->newStatement( 'P100', new StringValue( 'Foo' ) ),
			$this->newInstanceTypeIdStatement( 'Q1' ),
			$this->newStatement( 'P200', new StringValue( 'Bar' ) ),
			$this->newInstanceTypeIdStatement( 'Q2' )
		);

		$this->assertHasInstanceType( 'Q1', $statements );
	}

	public function testReturnsFirstHighestRankedInstanceTypeIdWithMultipleStatements(): void {
		$statement1 = $this->newInstanceTypeIdStatement( 'Q1' );
		$statement2 = $this->newInstanceTypeIdStatement( 'Q2' );
		$statement3 = $this->newInstanceTypeIdStatement( 'Q3' );
		$statement4 = $this->newInstanceTypeIdStatement( 'Q4' );

		$statement1->setRank( Statement::RANK_DEPRECATED );
		$statement2->setRank( Statement::RANK_PREFERRED );
		$statement3->setRank( Statement::RANK_NORMAL );
		$statement4->setRank( Statement::RANK_PREFERRED );

		$statements = new StatementList(
			$statement1,
			$statement2,
			$statement3,
			$statement4
		);

		$this->assertHasInstanceType( 'Q2', $statements );
	}

	public function testReturnsNullIfStatementsAreEmpty(): void {
		$this->assertHasNoInstanceType( new StatementList() );
	}

	private function assertHasNoInstanceType( StatementList $statements ): void {
		$this->assertNull( ( $this->newExtractor() )->getInstanceTypeId( $statements ) );
	}

	public function testReturnsNullIfInstanceTypeStatementDoesNotExist(): void {
		$statements = new StatementList(
			$this->newStatement( 'P100', new StringValue( 'Foo' ) ),
			$this->newStatement( 'P200', new StringValue( 'Bar' ) )
		);

		$this->assertHasNoInstanceType( $statements );
	}

	public function testReturnsNullIfInstanceTypeStatementHasNoValue(): void {
		$statements = new StatementList(
			new Statement(
				new PropertyNoValueSnak(
					new NumericPropertyId( self::INSTANCE_TYPE_ID )
				)
			)
		);

		$this->assertHasNoInstanceType( $statements );
	}

	public function testReturnsNullIfInstanceTypeStatementHasUnknownValue(): void {
		$statements = new StatementList(
			new Statement(
				new PropertySomeValueSnak(
					new NumericPropertyId( self::INSTANCE_TYPE_ID )
				)
			)
		);

		$this->assertHasNoInstanceType( $statements );
	}

	public function testReturnsInstanceTypeForFirstStatementWithValue(): void {
		$statements = new StatementList(
			new Statement(
				new PropertyNoValueSnak(
					new NumericPropertyId( self::INSTANCE_TYPE_ID ),
				)
			),
			new Statement(
				new PropertySomeValueSnak(
					new NumericPropertyId( self::INSTANCE_TYPE_ID )
				)
			),
			$this->newInstanceTypeIdStatement( 'Q1' )
		);

		$this->assertHasInstanceType( 'Q1', $statements );
	}

	/**
	 * @dataProvider notItemIdProvider
	 */
	public function testReturnsNullIfInstanceTypeIsNotItemId( DataValue $value ): void {
		$statements = new StatementList(
			$this->newStatement( self::INSTANCE_TYPE_ID, $value )
		);

		$this->assertHasNoInstanceType( $statements );
	}

	public function notItemIdProvider(): iterable {
		yield [
			new EntityIdValue( new NumericPropertyId( 'P100' ) )
		];
		yield [
			new StringValue( 'Q1' )
		];
		yield [
			new MonolingualTextValue( 'en', 'Q1' )
		];
		yield [
			new UnboundedQuantityValue( new DecimalValue( 123 ), '1' )
		];
		// TODO: more?
	}

}
