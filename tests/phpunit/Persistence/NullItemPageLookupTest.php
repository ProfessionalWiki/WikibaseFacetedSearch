<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use MediaWiki\Title\Title;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\NullPageItemLookup;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\NullPageItemLookup
 */
class NullItemPageLookupTest extends TestCase {

	public function testReturnsNull(): void {
		$this->assertNull(
			( new NullPageItemLookup() )->getItemId( Title::newFromText( 'Foo' ) )
		);
	}

}
