<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\FallbackItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubLabelLookup;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Term\Term;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\FallbackItemTypeLabelLookup
 */
class FallbackItemTypeLabelLookupTest extends TestCase {

	public function testReturnsTextFromLabelLookup(): void {
		$lookup = new FallbackItemTypeLabelLookup( new StubLabelLookup( label: new Term( 'whatever', 'expected' ) ) );

		$this->assertSame( 'expected', $lookup->getLabel( new ItemId( 'Q42' ) ) );
	}

	public function testReturnsIdSerializationWhenLabelLookupReturnsNull(): void {
		$lookup = new FallbackItemTypeLabelLookup( new StubLabelLookup( label: null ) );

		$this->assertSame( 'Q42', $lookup->getLabel( new ItemId( 'Q42' ) ) );
	}

}
