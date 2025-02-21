<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\MessageBuilder\ArrayMessageBuilder;
use ProfessionalWiki\MessageBuilder\MessageBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\FallbackItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubLabelLookup;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\Lookup\LabelLookup;
use Wikibase\DataModel\Term\Term;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\FallbackItemTypeLabelLookup
 */
class FallbackItemTypeLabelLookupTest extends TestCase {

	public function testReturnsMediaWikiMessageIfItExists(): void {
		$lookup = $this->newLookup( messageBuilder: new ArrayMessageBuilder( [
			'WikibaseFacetedSearch-item-type-Q41' => 'Cats',
			'WikibaseFacetedSearch-item-type-Q42' => 'People',
			'WikibaseFacetedSearch-item-type-Q43' => 'Dogs',
		] ) );

		$this->assertSame(
			'People',
			$lookup->getLabel( new ItemId( 'Q42' ) )
		);
	}

	private function newLookup(
		?LabelLookup $labelLookup = null,
		?MessageBuilder $messageBuilder = null
	): FallbackItemTypeLabelLookup {
		return new FallbackItemTypeLabelLookup(
			labelLookup: $labelLookup ?? new StubLabelLookup( label: null ),
			messageBuilder: $messageBuilder ?? new ArrayMessageBuilder( [] )
		);
	}

	public function testReturnsTextFromLabelLookupAsFallback(): void {
		$lookup = $this->newLookup( new StubLabelLookup( label: new Term( 'whatever', 'expected' ) ) );

		$this->assertSame(
			'expected',
			$lookup->getLabel( new ItemId( 'Q42' ) )
		);
	}

	public function testReturnsIdSerializationAsFinalFallback(): void {
		$lookup = $this->newLookup( new StubLabelLookup( label: null ) );

		$this->assertSame(
			'Q42',
			$lookup->getLabel( new ItemId( 'Q42' ) )
		);
	}

}
