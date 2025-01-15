<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\NullItemPageLookup;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\NullItemPageLookup
 */
class NullItemPageLookupTest extends TestCase {

	public function testReturnsNull(): void {
		$this->assertNull(
			( new NullItemPageLookup() )->getPageTitle( new ItemId( 'Q42' ) )
		);
	}

}
