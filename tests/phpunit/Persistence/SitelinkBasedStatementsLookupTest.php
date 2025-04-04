<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use DataValues\StringValue;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\SitelinkBasedStatementsLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\WikibaseFacetedSearchIntegrationTest;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Services\Lookup\InMemoryEntityLookup;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\Lib\Store\HashSiteLinkStore;
use WikiPage;

/**
 * @group Database
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\SitelinkBasedStatementsLookup
 */
class SitelinkBasedStatementsLookupTest extends WikibaseFacetedSearchIntegrationTest {

	private const SITE_ID = 'testSiteId';
	private const OTHER_SITE_ID = 'otherSiteId';
	private const ANOTHER_SITE_ID = 'anotherSiteId';
	private const CUSTOM_NAMESPACE = 3000;

	private HashSiteLinkStore $sitelinkStore;
	private InMemoryEntityLookup $entityLookup;
	private SitelinkBasedStatementsLookup $lookup;

	protected function setUp(): void {
		$this->sitelinkStore = new HashSiteLinkStore();
		$this->entityLookup = new InMemoryEntityLookup();
		$this->lookup = new SitelinkBasedStatementsLookup(
			self::SITE_ID,
			$this->sitelinkStore,
			$this->entityLookup
		);

		$this->setMwGlobals( 'wgExtraNamespaces', [
			self::CUSTOM_NAMESPACE => 'Custom'
		] );
	}

	public function testReturnsNoStatementsForPageWithoutIncomingSitelink(): void {
		$this->assertFindsStatementsForPage( $this->createPage(), [] );
	}

	private function assertFindsStatementsForPage( WikiPage $page, array $statements ): void {
		$this->assertEquals(
			$statements,
			$this->lookup->getStatements( $page )->toArray()
		);
	}

	public function testReturnsStatementsOfSitelinkingItem(): void {
		$item = new Item(
			id: new ItemId( 'Q1' ),
			statements: new StatementList(
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
			)
		);
		$page = $this->createPage();

		$this->entityLookup->addEntity( $item );
		$this->createSitelink( $item, $page );

		$this->assertFindsStatementsForPage(
			$page,
			[
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
			]
		);
	}

	private function createSitelink( Item $item, WikiPage $page, string $siteId = self::SITE_ID ): void {
		$item->getSiteLinkList()->addNewSiteLink( $siteId, $page->getTitle()->getPrefixedText() );
		$this->sitelinkStore->saveLinksOfItem( $item );
	}

	public function testReturnsNoStatementsIfSitelinkingItemHasNone(): void {
		$item = new Item(
			id: new ItemId( 'Q1' )
		);
		$page = $this->createPage();

		$this->entityLookup->addEntity( $item );
		$this->createSitelink( $item, $page );

		$this->assertFindsStatementsForPage( $page, [] );
	}

	public function testReturnsOnlyStatementsForItemsThatSitelinkPageWithCorrectSiteId(): void {
		$item = new Item(
			id: new ItemId( 'Q1' ),
			statements: new StatementList(
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
			)
		);
		$page = $this->createPage();

		$this->entityLookup->addEntity( $item );

		$this->createSitelink( $item, $page, self::OTHER_SITE_ID );

		$this->assertFindsStatementsForPage( $page, [] );
	}

	public function testReturnsStatementsOfOnlyTheItemThatSitelinksThePageWithCorrectSiteId(): void {
		$item1 = new Item(
			id: new ItemId( 'Q1' ),
			statements: new StatementList(
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
			)
		);
		$item2 = new Item(
			id: new ItemId( 'Q2' ),
			statements: new StatementList(
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P3' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P4' ), new StringValue( 'bar' ) ) )
			)
		);
		$item3 = new Item(
			id: new ItemId( 'Q3' ),
			statements: new StatementList(
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P5' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P6' ), new StringValue( 'bar' ) ) )
			)
		);
		$page = $this->createPage();

		$this->entityLookup->addEntity( $item1 );
		$this->entityLookup->addEntity( $item2 );
		$this->entityLookup->addEntity( $item3 );

		$this->createSitelink( $item1, $page, self::OTHER_SITE_ID );
		$this->createSitelink( $item2, $page );
		$this->createSitelink( $item3, $page, self::ANOTHER_SITE_ID );

		$this->assertFindsStatementsForPage(
			$page,
			[
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P3' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P4' ), new StringValue( 'bar' ) ) )
			]
		);
	}

	public function testReturnsStatementsEvenIfTheSitelinkedPageIsNamespaced(): void {
		$item = new Item(
			id: new ItemId( 'Q1' ),
			statements: new StatementList(
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
			)
		);
		$page = $this->createPage( namespace: self::CUSTOM_NAMESPACE );

		$this->entityLookup->addEntity( $item );
		$this->createSitelink( $item, $page );

		$this->assertFindsStatementsForPage(
			$page,
			[
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
			]
		);
	}

}
