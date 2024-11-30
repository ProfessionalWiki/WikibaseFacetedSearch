<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ItemPageLookupFactory;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\NullItemPageLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\SiteLinkItemPageLookup;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ItemPageLookupFactory
 */
class ItemPageLookupFactoryTest extends TestCase {

	public function testReturnsNullItemPageLookupWhenNoSiteIdIsConfigured(): void {
		$factory = new ItemPageLookupFactory(
			new Config()
		);

		$this->assertInstanceOf( NullItemPageLookup::class, $factory->newItemPageLookup() );
	}

	public function testReturnsSiteLinkItemPageLookupWhenSiteIdIsConfigured(): void {
		$factory = new ItemPageLookupFactory(
			new Config(
				linkTargetSitelinkSiteId: 'enwiki'
			)
		);

		$this->assertInstanceOf( SiteLinkItemPageLookup::class, $factory->newItemPageLookup() );
	}

}
