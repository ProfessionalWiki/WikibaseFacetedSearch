<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use MediaWiki\Title\Title;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\SitelinkPageItemLookup;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lib\Store\HashSiteLinkStore;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\SitelinkPageItemLookup
 */
class SitelinkItemPageLookupTest extends TestCase {

	private const SITE_ID = 'testSiteId';

	private HashSiteLinkStore $sitelinkStore;
	private SitelinkPageItemLookup $lookup;

	protected function setUp(): void {
		$this->sitelinkStore = new HashSiteLinkStore();
		$this->lookup = new SitelinkPageItemLookup(
			$this->sitelinkStore,
			self::SITE_ID
		);
	}

	public function testReturnsNullWhenNoSitelinkSiteIdIsConfigured(): void {
		$this->assertNull(
			( new SitelinkPageItemLookup(
				$this->sitelinkStore,
				null
			) )->getItemId( Title::newFromText( 'Foo' ) )
		);
	}

	public function testReturnsPageWhenSitelinkExists(): void {
		$this->createItemWithSitelink( 'Q42', self::SITE_ID, 'Page for Q42' );

		$this->assertPageHasItem( 'Page for Q42', 'Q42' );
	}

	private function createItemWithSitelink( string $itemId, string $siteId, string $pageName ): void {
		$item = new Item( new ItemId( $itemId ) );
		$item->getSiteLinkList()->addNewSiteLink( $siteId, $pageName );
		$this->sitelinkStore->saveLinksOfItem( $item );
	}

	private function assertPageHasItem( $pageTitle, string $itemId ): void {
		$this->assertEquals(
			$itemId,
			$this->lookup->getItemId( Title::newFromText( $pageTitle ) )?->getSerialization()
		);
	}

	public function testReturnsNullWhenNoSitelinksExist(): void {
		$this->assertNull( $this->lookup->getItemId( Title::newFromText( 'Foo' ) ) );
	}

	public function testReturnsNullWhenOnlyOtherSitelinksExist(): void {
		$this->createItemWithSitelink( 'Q100', 'otherSiteId', 'Other page' );
		$this->createItemWithSitelink( 'Q200', 'anotherSiteId', 'Another page' );

		$this->assertNull( $this->lookup->getItemId( Title::newFromText( 'Page without sitelink' ) ) );
	}

	public function testReturnsPageWhenManySitelinksExist(): void {
		$this->createItemWithSitelink( 'Q100', 'otherSiteId', 'Other page' );
		$this->createItemWithSitelink( 'Q42', self::SITE_ID, 'Page for Q42' );
		$this->createItemWithSitelink( 'Q200', 'anotherSiteId', 'Another page' );

		$this->assertPageHasItem( 'Page for Q42', 'Q42' );
	}

}
