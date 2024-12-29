<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use DataValues\StringValue;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\SiteLinkBasedStatementsLookup;
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
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\SiteLinkBasedStatementsLookup
 */
class SiteLinkBasedStatementsLookupTest extends WikibaseFacetedSearchIntegrationTest {

	private const SITE_ID = 'testSiteId';
	private const OTHER_SITE_ID = 'otherSiteId';
	private const ANOTHER_SITE_ID = 'anotherSiteId';

	private HashSiteLinkStore $siteLinkStore;
	private InMemoryEntityLookup $entityLookup;
	private SiteLinkBasedStatementsLookup $lookup;

	protected function setUp(): void {
		$this->siteLinkStore = new HashSiteLinkStore();
		$this->entityLookup = new InMemoryEntityLookup();
		$this->lookup = new SiteLinkBasedStatementsLookup(
			self::SITE_ID,
			$this->siteLinkStore,
			$this->entityLookup
		);
	}

	public function testPageWithoutSiteLinkReturnsNoStatements(): void {
		$this->assertPageHasStatements( $this->createPage(), [] );
	}

	private function assertPageHasStatements( WikiPage $page, array $statements ): void {
		$this->assertEquals(
			$statements,
			$this->lookup->getStatements( $page )->toArray()
		);
	}

	public function testPageWithSiteLinkToItemWithStatementsReturnsStatements(): void {
		$item = new Item(
			id: new ItemId( 'Q1' ),
			statements: new StatementList(
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
			)
		);
		$page = $this->createPage();

		$this->entityLookup->addEntity( $item );
		$this->createSiteLink( $item, $page );

		$this->assertPageHasStatements(
			$page,
			[
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
			]
		);
	}

	private function createSiteLink( Item $item, WikiPage $page, string $siteId = self::SITE_ID ): void {
		$item->getSiteLinkList()->addNewSiteLink( $siteId, $page->getTitle()->getText() );
		$this->siteLinkStore->saveLinksOfItem( $item );
	}

	public function testPageWithSiteLinkToItemWithoutStatementsReturnsNoStatements(): void {
		$item = new Item(
			id: new ItemId( 'Q1' )
		);
		$page = $this->createPage();

		$this->entityLookup->addEntity( $item );
		$this->createSiteLink( $item, $page );

		$this->assertPageHasStatements( $page, [] );
	}

	public function testPageWithDifferentSiteLinkToItemWithStatementsReturnsNoStatements(): void {
		$item = new Item(
			id: new ItemId( 'Q1' ),
			statements: new StatementList(
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P1' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P2' ), new StringValue( 'bar' ) ) )
			)
		);
		$page = $this->createPage();

		$this->entityLookup->addEntity( $item );

		$this->createSiteLink( $item, $page, self::OTHER_SITE_ID );

		$this->assertPageHasStatements( $page, [] );
	}

	public function testPageWithDifferentSiteLinkToItemWithoutStatementsReturnsNoStatements(): void {
		$item = new Item(
			id: new ItemId( 'Q1' ),
		);
		$page = $this->createPage();

		$this->entityLookup->addEntity( $item );

		$this->createSiteLink( $item, $page, self::OTHER_SITE_ID );

		$this->assertPageHasStatements( $page, [] );
	}

	public function testPageWithMultipleSiteLinksToItemsWithStatementsReturnsStatementsOfConfiguredSiteLink(): void {
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

		$this->createSiteLink( $item1, $page, self::OTHER_SITE_ID );
		$this->createSiteLink( $item2, $page );
		$this->createSiteLink( $item3, $page, self::ANOTHER_SITE_ID );

		$this->assertPageHasStatements(
			$page,
			[
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P3' ), new StringValue( 'foo' ) ) ),
				new Statement( new PropertyValueSnak( new NumericPropertyId( 'P4' ), new StringValue( 'bar' ) ) )
			]
		);
	}

}
