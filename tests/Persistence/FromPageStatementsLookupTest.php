<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use DataValues\StringValue;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\FromPageStatementsLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\WikibaseFacetedSearchIntegrationTest;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use WikiPage;

/**
 * @group Database
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\FromPageStatementsLookup
 */
class FromPageStatementsLookupTest extends WikibaseFacetedSearchIntegrationTest {

	public function testNormalPageReturnsNoStatements(): void {
		$this->assertPageHasStatements( $this->createPage(), [] );
	}

	private function assertPageHasStatements( WikiPage $page, array $statements ): void {
		$this->assertEquals(
			$statements,
			$this->newLookup()->getStatements( $page )->toArray()
		);
	}

	private function newLookup(): FromPageStatementsLookup {
		return new FromPageStatementsLookup();
	}

	public function testItemWithStatementsReturnsStatements(): void {
		$page = $this->createItemPage(
			new Item(
				id: new ItemId( 'Q1' ),
				statements: new StatementList(
					new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
					new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
				)
			)
		);

		$this->assertPageHasStatements(
			$page,
			[
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
			]
		);
	}

	public function testItemWithoutStatementsReturnsNoStatements(): void {
		$page = $this->createItemPage(
			new Item(
				id: new ItemId( 'Q1' )
			)
		);

		$this->assertPageHasStatements( $page, [] );
	}

	public function testPropertyWithStatementsReturnsNoStatements(): void {
		$page = $this->createPropertyPage(
			new Property(
				id: new NumericPropertyId( 'P1' ),
				fingerprint: null,
				dataTypeId: 'string',
				statements: new StatementList(
					new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
					new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
				)
			)
		);

		$this->assertPageHasStatements( $page, [] );
	}

	public function testPropertyWithoutStatementsReturnsNoStatements(): void {
		$page = $this->createPropertyPage(
			new Property(
				id: new NumericPropertyId( 'P1' ),
				fingerprint: null,
				dataTypeId: 'string'
			)
		);

		$this->assertPageHasStatements( $page, [] );
	}

}
