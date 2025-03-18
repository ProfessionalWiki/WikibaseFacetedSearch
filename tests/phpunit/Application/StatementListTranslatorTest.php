<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use DataValues\DataValue;
use DataValues\StringValue;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\StatementListTranslator;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubItemTypeExtractor;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubStatementTranslator;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\StatementListTranslator
 */
class StatementListTranslatorTest extends TestCase {

	private function newTranslator(
		?StubStatementTranslator $statementTranslator = null,
		?StubItemTypeExtractor $itemTypeExtractor = null,
		?Config $config = null
	): StatementListTranslator {
		return new StatementListTranslator(
			$statementTranslator ?? new StubStatementTranslator(),
			$itemTypeExtractor ?? new StubItemTypeExtractor(),
			$config ?? new Config()
		);
	}

	private function newStatement( PropertyId $propertyId, DataValue $value ): Statement {
		return new Statement(
			new PropertyValueSnak(
				$propertyId,
				$value
			)
		);
	}

	public function testReturnsEmptyArrayWhenNoItemType(): void {
		$statements = new StatementList();

		$this->assertSame(
			[],
			$this->newTranslator(
				itemTypeExtractor: new StubItemTypeExtractor( null )
			)->translateStatements( $statements )
		);
	}

	public function testTranslatesStatementsForAllConfiguredProperties(): void {
		$itemType = new ItemId( 'Q100' );
		$propertyP1 = new NumericPropertyId( 'P100' );
		$propertyP2 = new NumericPropertyId( 'P200' );

		$statements = new StatementList(
			$this->newStatement( $propertyP1, new StringValue( 'unimportant' ) ),
			$this->newStatement( $propertyP2, new StringValue( 'unimportant' ) ),
		);

		$config = new Config(
			itemTypeProperty: new NumericPropertyId( 'P42' ),
			facets: new FacetConfigList(
				new FacetConfig(
					itemType: $itemType,
					propertyId: $propertyP1,
					type: FacetType::LIST
				),
				new FacetConfig(
					itemType: $itemType,
					propertyId: $propertyP2,
					type: FacetType::LIST
				),
			)
		);

		$this->assertEquals(
			[
				'wbfs_P100' => [ 'translated value' ],
				'wbfs_P200' => [ 'translated value' ],
				'wbfs_P42' => [],
			],
			$this->newTranslator(
				statementTranslator: new StubStatementTranslator( 'translated value' ),
				itemTypeExtractor: new StubItemTypeExtractor( $itemType ),
				config: $config
			)->translateStatements( $statements )
		);
	}

	public function testFiltersNullValues(): void {
		$itemType = new ItemId( 'Q100' );
		$propertyP1 = new NumericPropertyId( 'P100' );

		$statements = new StatementList(
			$this->newStatement( $propertyP1, new StringValue( 'unimportant' ) )
		);

		$config = new Config(
			itemTypeProperty: new NumericPropertyId( 'P42' ),
			facets: new FacetConfigList(
				new FacetConfig(
					itemType: $itemType,
					propertyId: $propertyP1,
					type: FacetType::LIST
				)
			)
		);

		$this->assertSame(
			[
				'wbfs_P100' => [],
				'wbfs_P42' => [],
			],
			$this->newTranslator(
				statementTranslator: new StubStatementTranslator( null ),
				itemTypeExtractor: new StubItemTypeExtractor( $itemType ),
				config: $config
			)->translateStatements( $statements )
		);
	}

}
