<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\SiteLinkItemPageLookup;
use Title;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lib\Store\HashSiteLinkStore;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\SiteLinkItemPageLookup
 */
class SiteLinkItemPageLookupTest extends TestCase {

	private const SITE_ID = 'testSiteId';

	private HashSiteLinkStore $siteLinkStore;
	private SiteLinkItemPageLookup $lookup;

	protected function setUp(): void {
		$this->siteLinkStore = new HashSiteLinkStore();
		$this->lookup = new SiteLinkItemPageLookup(
			$this->siteLinkStore,
			self::SITE_ID
		);
	}

	public function testReturnsPageWhenSiteLinkExists(): void {
		$this->createItemWithSiteLink( 'Q42', self::SITE_ID, 'Page for Q42' );

		$this->assertItemPageHasTitle( 'Q42', 'Page for Q42' );
	}

	private function createItemWithSiteLink( string $itemId, string $siteId, string $pageName ): void {
		$item = new Item( new ItemId( $itemId ) );
		$item->getSiteLinkList()->addNewSiteLink( $siteId, $pageName );
		$this->siteLinkStore->saveLinksOfItem( $item );
	}

	private function assertItemPageHasTitle( string $itemId, $pageTitle ): void {
		$this->assertEquals(
			Title::newFromText( $pageTitle ),
			$this->lookup->getPageTitle( new ItemId( $itemId ) )
		);
	}

	public function testReturnsNullWhenNoSiteLinksExist(): void {
		$this->assertNull( $this->lookup->getPageTitle( new ItemId( 'Q404' ) ) );
	}

	public function testReturnsNullWhenOnlyOtherSiteLinksExist(): void {
		$this->createItemWithSiteLink( 'Q100', 'otherSiteId', 'Other page' );
		$this->createItemWithSiteLink( 'Q200', 'anotherSiteId', 'Another page' );

		$this->assertNull( $this->lookup->getPageTitle( new ItemId( 'Q42' ) ) );
	}

	public function testReturnsPageWhenManySiteLinksExist(): void {
		$this->createItemWithSiteLink( 'Q100', 'otherSiteId', 'Other page' );
		$this->createItemWithSiteLink( 'Q42', self::SITE_ID, 'Page for Q42' );
		$this->createItemWithSiteLink( 'Q200', 'anotherSiteId', 'Another page' );

		$this->assertItemPageHasTitle( 'Q42', 'Page for Q42' );
	}

}
