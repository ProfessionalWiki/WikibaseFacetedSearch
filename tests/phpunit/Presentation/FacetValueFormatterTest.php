<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetValueFormatter;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubPropertyDataTypeLookup;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetValueFormatter
 */
class FacetValueFormatterTest extends TestCase {

	public function testGetLabelWithWikibaseItemDataType(): void {
		$valueFormatter = $this->newFacetValueFormatter();

		$this->assertSame( 'Q100', $valueFormatter->getLabel( 'Q100', new NumericPropertyId( 'P123' ) ) );
	}

	public function testGetLabelWithNonWikibaseItemDataType(): void {
		$valueFormatter = $this->newFacetValueFormatter( new StubPropertyDataTypeLookup( 'not-wikibase-item' ) );

		$this->assertSame( 'unimportant', $valueFormatter->getLabel( 'unimportant', new NumericPropertyId( 'P123' ) ) );
	}

	public function testGetLabelWithNullDataType(): void {
		$valueFormatter = $this->newFacetValueFormatter( new StubPropertyDataTypeLookup( null ) );

		$this->assertSame( 'unimportant', $valueFormatter->getLabel( 'unimportant', new NumericPropertyId( 'P123' ) ) );
	}

	private function newFacetValueFormatter( ?StubPropertyDataTypeLookup $dataTypeLookup = null, ?StubLabelLookup $labelLookup = null ): FacetValueFormatter {
		return new FacetValueFormatter(
			$dataTypeLookup ?? new StubPropertyDataTypeLookup( 'wikibase-item' ),
			$labelLookup ?? new StubLabelLookup( null )
		);
	}

}
