<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\ListFacetHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubValueCounter;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\ListFacetHtmlBuilder
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 */
class ListFacetHtmlBuilderTest extends TestCase {

	public function testRendersTemplate(): void {
		$html = $this->newListFacetHtmlBuilder()->buildHtml(
			config: new FacetConfig(
				instanceTypeId: new ItemId( 'Q123' ),
				propertyId: new NumericPropertyId( 'P42' ),
				type: FacetType::LIST
			),
			state: new PropertyConstraints( propertyId: new NumericPropertyId( 'P42' ) )
		);

		$this->assertStringContainsString( 'P42', $html );
		$this->assertStringContainsString( StubValueCounter::FIRST_VALUE, $html );
		$this->assertStringContainsString( StubValueCounter::SECOND_VALUE, $html );
		$this->assertStringContainsString( StubValueCounter::THIRD_VALUE, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::FIRST_COUNT, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::SECOND_COUNT, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::THIRD_COUNT, $html );
	}

	private function newListFacetHtmlBuilder(): ListFacetHtmlBuilder {
		return new ListFacetHtmlBuilder(
			parser: WikibaseFacetedSearchExtension::getInstance()->getTemplateParser(),
			valueCounter: new StubValueCounter()
		);
	}

	public function testCreatesCheckboxesViewModel(): void {
		$viewModel = $this->newListFacetHtmlBuilder()->buildViewModel(
			config: new FacetConfig(
				instanceTypeId: new ItemId( 'Q123' ),
				propertyId: new NumericPropertyId( 'P42' ),
				type: FacetType::LIST
			),
			state: new PropertyConstraints( propertyId: new NumericPropertyId( 'P42' ) )
		);

		$this->assertArrayHasKey( 'checkboxes', $viewModel );
		$this->assertIsArray( $viewModel['checkboxes'] );
		$this->assertSame( StubValueCounter::FIRST_VALUE, $viewModel['checkboxes'][0]['label'] );
		$this->assertSame( StubValueCounter::SECOND_VALUE, $viewModel['checkboxes'][1]['label'] );
		$this->assertSame( StubValueCounter::THIRD_VALUE, $viewModel['checkboxes'][2]['label'] );
		$this->assertCount( 3, $viewModel['checkboxes'] );
	}

}
