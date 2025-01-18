<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use DataValues\DataValue;
use DataValues\DecimalValue;
use DataValues\MonolingualTextValue;
use DataValues\StringValue;
use DataValues\UnboundedQuantityValue;
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
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeExtractor
 */
class ItemTypeExtractorTest extends TestCase {

	private const INSTANCE_OF_ID = 'P42';

	public function testReturnsItemTypeWithSingleInstanceOfStatement(): void {
		$statements = new StatementList(
			$this->newStatement( 'P100', new StringValue( 'Foo' ) ),
			$this->newInstanceOfStatement( 'Q1' ),
			$this->newStatement( 'P200', new StringValue( 'Bar' ) )
		);

		$this->assertHasItemType( 'Q1', $statements );
	}

	private function newStatement( string $propertyId, DataValue $value ): Statement {
		return new Statement(
			new PropertyValueSnak(
				new NumericPropertyId( $propertyId ),
				$value
			)
		);
	}

	private function newInstanceOfStatement( string $itemId ): Statement {
		return $this->newStatement( self::INSTANCE_OF_ID, new EntityIdValue( new ItemId( $itemId ) ) );
	}

	private function assertHasItemType( string $itemType, StatementList $statements ): void {
		$this->assertEquals(
			new ItemId( $itemType ),
			( $this->newExtractor() )->getItemType( $statements )
		);
	}

	private function newExtractor(): ItemTypeExtractor {
		return new ItemTypeExtractor(
			new NumericPropertyId( self::INSTANCE_OF_ID )
		);
	}

	public function testReturnsFirstItemTypeIdWithMultipleInstanceOfStatements(): void {
		$statements = new StatementList(
			$this->newStatement( 'P100', new StringValue( 'Foo' ) ),
			$this->newInstanceOfStatement( 'Q1' ),
			$this->newStatement( 'P200', new StringValue( 'Bar' ) ),
			$this->newInstanceOfStatement( 'Q2' )
		);

		$this->assertHasItemType( 'Q1', $statements );
	}

	public function testReturnsFirstHighestRankedItemTypeIdWithMultipleStatements(): void {
		$statement1 = $this->newInstanceOfStatement( 'Q1' );
		$statement2 = $this->newInstanceOfStatement( 'Q2' );
		$statement3 = $this->newInstanceOfStatement( 'Q3' );
		$statement4 = $this->newInstanceOfStatement( 'Q4' );
		$statement5 = $this->newInstanceOfStatement( 'Q5' );

		$statement1->setRank( Statement::RANK_DEPRECATED );
		$statement2->setRank( Statement::RANK_NORMAL );
		$statement3->setRank( Statement::RANK_PREFERRED );
		$statement4->setRank( Statement::RANK_PREFERRED );
		$statement5->setRank( Statement::RANK_NORMAL );

		$statements = new StatementList(
			$statement1,
			$statement2,
			$statement3,
			$statement4,
			$statement5
		);

		$this->assertHasItemType( 'Q3', $statements );
	}

	public function testReturnsNullIfStatementsAreEmpty(): void {
		$this->assertHasNoItemType( new StatementList() );
	}

	private function assertHasNoItemType( StatementList $statements ): void {
		$this->assertNull( ( $this->newExtractor() )->getItemType( $statements ) );
	}

	public function testReturnsNullIfThereIsNoInstanceOfStatement(): void {
		$statements = new StatementList(
			$this->newStatement( 'P100', new StringValue( 'Foo' ) ),
			$this->newStatement( 'P200', new StringValue( 'Bar' ) )
		);

		$this->assertHasNoItemType( $statements );
	}

	public function testReturnsNullIfItemTypeStatementHasNoValue(): void {
		$statements = new StatementList(
			new Statement(
				new PropertyNoValueSnak(
					new NumericPropertyId( self::INSTANCE_OF_ID )
				)
			)
		);

		$this->assertHasNoItemType( $statements );
	}

	public function testReturnsNullIfItemTypeStatementHasUnknownValue(): void {
		$statements = new StatementList(
			new Statement(
				new PropertySomeValueSnak(
					new NumericPropertyId( self::INSTANCE_OF_ID )
				)
			)
		);

		$this->assertHasNoItemType( $statements );
	}

	public function testReturnsItemTypeForFirstStatementWithValue(): void {
		$statements = new StatementList(
			new Statement(
				new PropertyNoValueSnak(
					new NumericPropertyId( self::INSTANCE_OF_ID ),
				)
			),
			new Statement(
				new PropertySomeValueSnak(
					new NumericPropertyId( self::INSTANCE_OF_ID )
				)
			),
			$this->newInstanceOfStatement( 'Q1' )
		);

		$this->assertHasItemType( 'Q1', $statements );
	}

	/**
	 * @dataProvider notItemIdProvider
	 */
	public function testReturnsNullIfItemTypeIsNotItemId( DataValue $value ): void {
		$statements = new StatementList(
			$this->newStatement( self::INSTANCE_OF_ID, $value )
		);

		$this->assertHasNoItemType( $statements );
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
