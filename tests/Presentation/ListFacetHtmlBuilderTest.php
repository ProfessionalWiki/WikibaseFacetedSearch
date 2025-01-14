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

	private const FACET_PROPERTY_ID = 'P42';

	public function testRendersTemplate(): void {
		$html = $this->newListFacetHtmlBuilder()->buildHtml(
			config: new FacetConfig(
				instanceTypeId: new ItemId( 'Q123' ),
				propertyId: new NumericPropertyId( self::FACET_PROPERTY_ID ),
				type: FacetType::LIST
			),
			state: $this->newPropertyConstraints()
		);

		$this->assertStringContainsString( self::FACET_PROPERTY_ID, $html );
		$this->assertStringContainsString( StubValueCounter::FIRST_VALUE, $html );
		$this->assertStringContainsString( StubValueCounter::SECOND_VALUE, $html );
		$this->assertStringContainsString( StubValueCounter::THIRD_VALUE, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::FIRST_COUNT, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::SECOND_COUNT, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::THIRD_COUNT, $html );
	}

	private function newPropertyConstraints(): PropertyConstraints {
		return new PropertyConstraints( propertyId: new NumericPropertyId( self::FACET_PROPERTY_ID ) );
	}

	private function newListFacetHtmlBuilder(): ListFacetHtmlBuilder {
		return new ListFacetHtmlBuilder(
			parser: WikibaseFacetedSearchExtension::getInstance()->getTemplateParser(),
			valueCounter: new StubValueCounter()
		);
	}

	public function testCheckboxesViewModelContainsAllValues(): void {
		$viewModel = $this->buildViewModel();

		$this->assertArrayHasKey( 'checkboxes', $viewModel );
		$this->assertIsArray( $viewModel['checkboxes'] );
		$this->assertSame( StubValueCounter::FIRST_VALUE, $viewModel['checkboxes'][0]['label'] );
		$this->assertSame( StubValueCounter::SECOND_VALUE, $viewModel['checkboxes'][1]['label'] );
		$this->assertSame( StubValueCounter::THIRD_VALUE, $viewModel['checkboxes'][2]['label'] );
		$this->assertCount( 3, $viewModel['checkboxes'] );
	}

	private function buildViewModel( ?PropertyConstraints $constraints = null ): array {
		return $this->newListFacetHtmlBuilder()->buildViewModel(
			config: new FacetConfig(
				instanceTypeId: new ItemId( 'Q123' ),
				propertyId: new NumericPropertyId( self::FACET_PROPERTY_ID ),
				type: FacetType::LIST
			),
			state: $constraints ?? $this->newPropertyConstraints()
		);
	}

	public function testCheckboxesViewModelContainsAllCounts(): void {
		$viewModel = $this->buildViewModel();

		$this->assertSame( StubValueCounter::FIRST_COUNT, $viewModel['checkboxes'][0]['count'] );
		$this->assertSame( StubValueCounter::SECOND_COUNT, $viewModel['checkboxes'][1]['count'] );
		$this->assertSame( StubValueCounter::THIRD_COUNT, $viewModel['checkboxes'][2]['count'] );
	}

	public function testOnlyCheckboxesForValuesInThePropertyConstraintsAreChecked_andCase(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints()
				->withAdditionalAndValue( 'mismatch' )
				->withAdditionalAndValue( StubValueCounter::SECOND_VALUE )
				->withAdditionalAndValue( 'another mismatch' )
		);

		$this->assertFalse( $viewModel['checkboxes'][0]['checked'] );
		$this->assertTrue( $viewModel['checkboxes'][1]['checked'] );
		$this->assertFalse( $viewModel['checkboxes'][2]['checked'] );
	}

	public function testOnlyCheckboxesForValuesInThePropertyConstraintsAreChecked_orCase(): void {
		$this->markTestSkipped( 'TODO: implement OR' );

		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints()->withOrValues(
				'mismatch',
				StubValueCounter::SECOND_VALUE,
				'another mismatch'
			)
		);

		$this->assertFalse( $viewModel['checkboxes'][0]['checked'] );
		$this->assertTrue( $viewModel['checkboxes'][1]['checked'] );
		$this->assertFalse( $viewModel['checkboxes'][2]['checked'] );
	}

}
