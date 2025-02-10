<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use Elastica\Query\MatchAll;
use MediaWiki\MediaWikiServices;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounter;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\ListFacetHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SequentialValueCounter;
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
			config: $this->newFacetConfig(),
			state: $this->newPropertyConstraints(),
			currentQuery: new MatchAll()
		);

		$this->assertStringContainsString( self::FACET_PROPERTY_ID, $html );
		$this->assertStringContainsString( StubValueCounter::FIRST_VALUE, $html );
		$this->assertStringContainsString( StubValueCounter::SECOND_VALUE, $html );
		$this->assertStringContainsString( StubValueCounter::THIRD_VALUE, $html );
		$this->assertStringContainsString( StubValueCounter::FOURTH_VALUE, $html );
		$this->assertStringContainsString( StubValueCounter::FIFTH_VALUE, $html );
		$this->assertStringContainsString( StubValueCounter::SIXTH_VALUE, $html );
		$this->assertStringContainsString( StubValueCounter::SEVENTH_VALUE, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::FIRST_COUNT, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::SECOND_COUNT, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::THIRD_COUNT, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::FOURTH_COUNT, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::FIFTH_COUNT, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::SIXTH_COUNT, $html );
		$this->assertStringContainsString( 'count">' . StubValueCounter::SEVENTH_COUNT, $html );
		$this->assertStringContainsString( 'overflow-button-show', $html );
	}

	private function newPropertyConstraints(): PropertyConstraints {
		return new PropertyConstraints( propertyId: new NumericPropertyId( self::FACET_PROPERTY_ID ) );
	}

	private function newListFacetHtmlBuilder( ?ValueCounter $valueCounter = null ): ListFacetHtmlBuilder {
		return new ListFacetHtmlBuilder(
			parser: WikibaseFacetedSearchExtension::getInstance()->getTemplateParser(),
			valueCounter: $valueCounter ?? new StubValueCounter(),
			valueFormatter: WikibaseFacetedSearchExtension::getInstance()->getFacetValueFormatter( MediaWikiServices::getInstance()->getContentLanguage() )
		);
	}

	public function testCheckboxesViewModelContainsAllValues(): void {
		$viewModel = $this->buildViewModel();

		$this->assertArrayHasKey( 'checkboxes', $viewModel );

		$this->assertArrayHasKey( 'visible', $viewModel['checkboxes'] );
		$this->assertSame( StubValueCounter::FIRST_VALUE, $viewModel['checkboxes']['visible'][0]['formattedValue'] );
		$this->assertSame( StubValueCounter::SECOND_VALUE, $viewModel['checkboxes']['visible'][1]['formattedValue'] );
		$this->assertSame( StubValueCounter::THIRD_VALUE, $viewModel['checkboxes']['visible'][2]['formattedValue'] );
		$this->assertSame( StubValueCounter::FOURTH_VALUE, $viewModel['checkboxes']['visible'][3]['formattedValue'] );
		$this->assertSame( StubValueCounter::FIFTH_VALUE, $viewModel['checkboxes']['visible'][4]['formattedValue'] );
		$this->assertCount( 5, $viewModel['checkboxes']['visible'] );

		$this->assertArrayHasKey( 'collapsed', $viewModel['checkboxes'] );
		$this->assertSame( StubValueCounter::SIXTH_VALUE, $viewModel['checkboxes']['collapsed'][0]['formattedValue'] );
		$this->assertSame( StubValueCounter::SEVENTH_VALUE, $viewModel['checkboxes']['collapsed'][1]['formattedValue'] );
		$this->assertCount( 2, $viewModel['checkboxes']['collapsed'] );
	}

	private function buildViewModel(
		?PropertyConstraints $constraints = null,
		array $typeSpecificConfig = [],
		?ValueCounter $valueCounter = null
	): array {
		return $this->newListFacetHtmlBuilder( $valueCounter )->buildViewModel(
			config: $this->newFacetConfig( $typeSpecificConfig ),
			state: $constraints ?? $this->newPropertyConstraints(),
			currentQuery: new MatchAll()
		);
	}

	private function newFacetConfig( array $typeSpecificConfig = [] ): FacetConfig {
		return new FacetConfig(
			itemType: new ItemId( 'Q123' ),
			propertyId: new NumericPropertyId( self::FACET_PROPERTY_ID ),
			type: FacetType::LIST,
			typeSpecificConfig: $typeSpecificConfig
		);
	}

	public function testCheckboxesViewModelContainsAllCounts(): void {
		$viewModel = $this->buildViewModel();

		$this->assertSame( StubValueCounter::FIRST_COUNT, $viewModel['checkboxes']['visible'][0]['count'] );
		$this->assertSame( StubValueCounter::SECOND_COUNT, $viewModel['checkboxes']['visible'][1]['count'] );
		$this->assertSame( StubValueCounter::THIRD_COUNT, $viewModel['checkboxes']['visible'][2]['count'] );
		$this->assertSame( StubValueCounter::FOURTH_COUNT, $viewModel['checkboxes']['visible'][3]['count'] );
		$this->assertSame( StubValueCounter::FIFTH_COUNT, $viewModel['checkboxes']['visible'][4]['count'] );

		$this->assertSame( StubValueCounter::SIXTH_COUNT, $viewModel['checkboxes']['collapsed'][0]['count'] );
		$this->assertSame( StubValueCounter::SEVENTH_COUNT, $viewModel['checkboxes']['collapsed'][1]['count'] );
	}

	public function testOnlyCheckboxesForValuesInThePropertyConstraintsAreChecked_andCase(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints()
				->withAdditionalAndValue( 'mismatch' )
				->withAdditionalAndValue( StubValueCounter::SECOND_VALUE )
				->withAdditionalAndValue( 'another mismatch' )
				->withAdditionalAndValue( StubValueCounter::SIXTH_VALUE )
		);

		$this->assertFalse( $viewModel['checkboxes']['visible'][0]['checked'] );
		$this->assertTrue( $viewModel['checkboxes']['visible'][1]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['visible'][2]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['visible'][3]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['visible'][4]['checked'] );

		$this->assertTrue( $viewModel['checkboxes']['collapsed'][0]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['collapsed'][1]['checked'] );
	}

	public function testOnlyCheckboxesForValuesInThePropertyConstraintsAreChecked_orCase(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints()->withOrValues(
				'mismatch',
				StubValueCounter::SECOND_VALUE,
				'another mismatch',
				StubValueCounter::SIXTH_VALUE
			),
			typeSpecificConfig: [ 'defaultCombineWith' => 'OR' ]
		);

		$this->assertFalse( $viewModel['checkboxes']['visible'][0]['checked'] );
		$this->assertTrue( $viewModel['checkboxes']['visible'][1]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['visible'][2]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['visible'][3]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['visible'][4]['checked'] );

		$this->assertTrue( $viewModel['checkboxes']['collapsed'][0]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['collapsed'][1]['checked'] );
	}

	public function testAndIsDisabledWhenOrIsSelectedAndChoiceIsDisabled(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints(),
			typeSpecificConfig: [ 'allowCombineWithChoice' => false, 'defaultCombineWith' => 'OR' ]
		);

		$this->assertTrue( $viewModel['toggle']['and']['disabled'] );
		$this->assertFalse( $viewModel['toggle']['or']['disabled'] );
	}

	public function testOrIsDisabledWhenAndIsSelectedAndChoiceIsDisabled(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints(),
			typeSpecificConfig: [ 'allowCombineWithChoice' => false, 'defaultCombineWith' => 'AND' ]
		);

		$this->assertFalse( $viewModel['toggle']['and']['disabled'] );
		$this->assertTrue( $viewModel['toggle']['or']['disabled'] );
	}

	public function testToggleIsFullyEnabledWhenChoiceIsAllowed(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints(),
			typeSpecificConfig: [ 'allowCombineWithChoice' => true ]
		);

		$this->assertFalse( $viewModel['toggle']['and']['disabled'] );
		$this->assertFalse( $viewModel['toggle']['or']['disabled'] );
	}

	public function testShouldCombineWithAndByDefault(): void {
		$this->assertTrue(
			$this->newListFacetHtmlBuilder()->shouldCombineWithAnd(
				$this->newFacetConfig(),
				$this->newPropertyConstraints()
			)
		);
	}

	public function testShouldCombineWithOrWhenConfiguredAsDefault(): void {
		$this->assertFalse(
			$this->newListFacetHtmlBuilder()->shouldCombineWithAnd(
				$this->newFacetConfig( [ 'defaultCombineWith' => 'OR' ] ),
				$this->newPropertyConstraints()
			)
		);
	}

	public function testShouldCombineWithAndWhenSpecifiedByConstraintsAndAllowed(): void {
		$this->assertTrue(
			$this->newListFacetHtmlBuilder()->shouldCombineWithAnd(
				$this->newFacetConfig( [ 'defaultCombineWith' => 'OR', 'allowCombineWithChoice' => true ] ),
				$this->newPropertyConstraints()->withAdditionalAndValue( 'a' )->withAdditionalAndValue( 'b' )
			)
		);
	}

	public function testShouldCombineWithConfiguredDefaultOrWhenConstraintIsNotAllowed(): void {
		$this->assertFalse(
			$this->newListFacetHtmlBuilder()->shouldCombineWithAnd(
				$this->newFacetConfig( [ 'defaultCombineWith' => 'OR', 'allowCombineWithChoice' => false ] ),
				$this->newPropertyConstraints()->withAdditionalAndValue( 'a' )->withAdditionalAndValue( 'b' )
			)
		);
	}

	public function testShouldCombineWithConfiguredDefaultAndWhenConstraintIsNotAllowed(): void {
		$this->assertTrue(
			$this->newListFacetHtmlBuilder()->shouldCombineWithAnd(
				$this->newFacetConfig( [ 'defaultCombineWith' => 'AND', 'allowCombineWithChoice' => false ] ),
				$this->newPropertyConstraints()->withOrValues( 'a', 'b' )
			)
		);
	}

	public function testShouldCombineWithOrWhenSpecifiedByConstraintsAndAllowed(): void {
		$this->assertFalse(
			$this->newListFacetHtmlBuilder()->shouldCombineWithAnd(
				$this->newFacetConfig( [ 'defaultCombineWith' => 'AND', 'allowCombineWithChoice' => true ] ),
				$this->newPropertyConstraints()->withOrValues( 'a', 'b' )
			)
		);
	}

	public function testOrIsSelectedWhenDefault(): void {
		$viewModel = $this->buildViewModel(
			typeSpecificConfig: [ 'defaultCombineWith' => 'OR' ]
		);

		$this->assertFalse( $viewModel['toggle']['and']['selected'] );
		$this->assertTrue( $viewModel['toggle']['or']['selected'] );
	}

	public function testAndIsSelectedWhenDefault(): void {
		$viewModel = $this->buildViewModel(
			typeSpecificConfig: [ 'defaultCombineWith' => 'AND' ]
		);

		$this->assertTrue( $viewModel['toggle']['and']['selected'] );
		$this->assertFalse( $viewModel['toggle']['or']['selected'] );
	}

	public function testContainsOnlyVisibleCheckboxesIfCountLessThanBoundary(): void {
		$viewModel = $this->buildViewModel(
			valueCounter: new SequentialValueCounter( 4 )
		);

		$this->assertCount( 4, $viewModel['checkboxes']['visible'] );
		$this->assertCount( 0, $viewModel['checkboxes']['collapsed'] );
		$this->assertFalse( $viewModel['checkboxes']['showMore'] );
	}

	public function testContainsOnlyVisibleCheckboxesIfCountEqualsBoundary(): void {
		$viewModel = $this->buildViewModel(
			valueCounter: new SequentialValueCounter( 5 )
		);

		$this->assertCount( 5, $viewModel['checkboxes']['visible'] );
		$this->assertCount( 0, $viewModel['checkboxes']['collapsed'] );
		$this->assertFalse( $viewModel['checkboxes']['showMore'] );
	}

	public function testContainsVisibleAndCollapsedCheckboxesIfCountGreaterThanBoundary(): void {
		$viewModel = $this->buildViewModel(
			valueCounter: new SequentialValueCounter( 6 )
		);

		$this->assertCount( 5, $viewModel['checkboxes']['visible'] );
		$this->assertCount( 1, $viewModel['checkboxes']['collapsed'] );
		$this->assertTrue( $viewModel['checkboxes']['showMore'] );
	}

}
