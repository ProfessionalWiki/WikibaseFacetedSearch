<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\NullPageItemLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageItemLookupFactory;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\SitelinkPageItemLookup;
use Wikibase\Lib\Store\HashSiteLinkStore;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageItemLookupFactory
 */
class ItemPageLookupFactoryTest extends TestCase {

	public function testReturnsNullItemPageLookupWhenNoSiteIdIsConfigured(): void {
		$factory = new PageItemLookupFactory(
			config: new Config(),
			sitelinkLookup: new HashSiteLinkStore()
		);

		$this->assertInstanceOf( NullPageItemLookup::class, $factory->newPageItemLookup() );
	}

	public function testReturnsSitelinkItemPageLookupWhenSiteIdIsConfigured(): void {
		$factory = new PageItemLookupFactory(
			new Config(
				linkTargetSitelinkSiteId: 'enwiki'
			),
			sitelinkLookup: new HashSiteLinkStore()
		);

		$this->assertInstanceOf( SitelinkPageItemLookup::class, $factory->newPageItemLookup() );
	}

}
