<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use MediaWiki\Title\Title;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\SitelinkItemPageLookup;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lib\Store\HashSiteLinkStore;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\SitelinkItemPageLookup
 */
class SitelinkItemPageLookupTest extends TestCase {

	private const SITE_ID = 'testSiteId';

	private HashSiteLinkStore $sitelinkStore;
	private SitelinkItemPageLookup $lookup;

	protected function setUp(): void {
		$this->sitelinkStore = new HashSiteLinkStore();
		$this->lookup = new SitelinkItemPageLookup(
			$this->sitelinkStore,
			self::SITE_ID
		);
	}

	public function testReturnsPageWhenSitelinkExists(): void {
		$this->createItemWithSitelink( 'Q42', self::SITE_ID, 'Page for Q42' );

		$this->assertItemPageHasTitle( 'Q42', 'Page for Q42' );
	}

	private function createItemWithSitelink( string $itemId, string $siteId, string $pageName ): void {
		$item = new Item( new ItemId( $itemId ) );
		$item->getSiteLinkList()->addNewSiteLink( $siteId, $pageName );
		$this->sitelinkStore->saveLinksOfItem( $item );
	}

	private function assertItemPageHasTitle( string $itemId, $pageTitle ): void {
		$this->assertEquals(
			Title::newFromText( $pageTitle ),
			$this->lookup->getPageTitle( new ItemId( $itemId ) )
		);
	}

	public function testReturnsNullWhenNoSitelinksExist(): void {
		$this->assertNull( $this->lookup->getPageTitle( new ItemId( 'Q404' ) ) );
	}

	public function testReturnsNullWhenOnlyOtherSitelinksExist(): void {
		$this->createItemWithSitelink( 'Q100', 'otherSiteId', 'Other page' );
		$this->createItemWithSitelink( 'Q200', 'anotherSiteId', 'Another page' );

		$this->assertNull( $this->lookup->getPageTitle( new ItemId( 'Q42' ) ) );
	}

	public function testReturnsPageWhenManySitelinksExist(): void {
		$this->createItemWithSitelink( 'Q100', 'otherSiteId', 'Other page' );
		$this->createItemWithSitelink( 'Q42', self::SITE_ID, 'Page for Q42' );
		$this->createItemWithSitelink( 'Q200', 'anotherSiteId', 'Another page' );

		$this->assertItemPageHasTitle( 'Q42', 'Page for Q42' );
	}

}
