<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetLabelBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetLabelBuilder
 */
class FacetLabelBuilderTest extends TestCase {

	public function testGetLabel(): void {
		$facetLabelBuilder = $this->newFacetLabelBuilder();

		$this->assertSame( 'Q100', $facetLabelBuilder->getLabel( 'Q100', new NumericPropertyId( 'P123' ) ) );
	}

	private function newFacetLabelBuilder(): FacetLabelBuilder {
		return new FacetLabelBuilder(
			$this->newPropertyDataTypeLookup(),
			new StubLabelLookup( null )
		);
	}

	private function newPropertyDataTypeLookup(): PropertyDataTypeLookup {
		return WikibaseFacetedSearchExtension::getInstance()->getPropertyDataTypeLookup();
	}
}
