<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

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
			state: $this->newPropertyConstraints()
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

	public function testRendersTemplateWhenNoValues(): void {
		$html = $this->newListFacetHtmlBuilder(
			valueCounter: new SequentialValueCounter( 0 )
		)->buildHtml(
			config: $this->newFacetConfig(),
			state: $this->newPropertyConstraints()
		);

		$this->assertSame( '', $html );
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

	public function testCheckboxesViewModelContainsNoValues(): void {
		$viewModel = $this->buildViewModel(
			valueCounter: new SequentialValueCounter( 0 )
		);

		$this->assertArrayHasKey( 'visible', $viewModel['checkboxes'] );
		$this->assertArrayHasKey( 'collapsed', $viewModel['checkboxes'] );
		$this->assertArrayHasKey( 'showMore', $viewModel['checkboxes'] );

		$this->assertCount( 0, $viewModel['checkboxes']['visible'] );
		$this->assertCount( 0, $viewModel['checkboxes']['collapsed'] );
		$this->assertFalse( $viewModel['checkboxes']['showMore'] );
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
		$htmlBuilder = $this->newListFacetHtmlBuilder( $valueCounter );
		$facetConfig = $this->newFacetConfig( $typeSpecificConfig );
		$state = $constraints ?? $this->newPropertyConstraints();

		return $htmlBuilder->buildViewModel(
			config: $facetConfig,
			state: $state,
			valueCounts: $htmlBuilder->getValuesAndCounts( $state )
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

		$this->assertTrue( $viewModel['checkboxes']['visible'][0]['checked'] );
		$this->assertSame( StubValueCounter::SECOND_VALUE, $viewModel['checkboxes']['visible'][0]['value'] );
		$this->assertTrue( $viewModel['checkboxes']['visible'][1]['checked'] );
		$this->assertSame( StubValueCounter::SIXTH_VALUE, $viewModel['checkboxes']['visible'][1]['value'] );

		$this->assertFalse( $viewModel['checkboxes']['visible'][2]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['visible'][3]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['visible'][4]['checked'] );

		$this->assertFalse( $viewModel['checkboxes']['collapsed'][0]['checked'] );
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

		$this->assertTrue( $viewModel['checkboxes']['visible'][0]['checked'] );
		$this->assertSame( StubValueCounter::SECOND_VALUE, $viewModel['checkboxes']['visible'][0]['value'] );
		$this->assertTrue( $viewModel['checkboxes']['visible'][1]['checked'] );
		$this->assertSame( StubValueCounter::SIXTH_VALUE, $viewModel['checkboxes']['visible'][1]['value'] );

		$this->assertFalse( $viewModel['checkboxes']['visible'][2]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['visible'][3]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['visible'][4]['checked'] );

		$this->assertFalse( $viewModel['checkboxes']['collapsed'][0]['checked'] );
		$this->assertFalse( $viewModel['checkboxes']['collapsed'][1]['checked'] );
	}

	public function testGetFacetModeByDefault(): void {
		$this->assertSame(
			ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_AND,
			$this->newListFacetHtmlBuilder()->getFacetMode(
				$this->newFacetConfig(),
				$this->newPropertyConstraints()
			)
		);
	}

	public function testGetFacetModeWhenConfiguredAsDefault(): void {
		$this->assertSame(
			ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_OR,
			$this->newListFacetHtmlBuilder()->getFacetMode(
				$this->newFacetConfig( [ 'defaultCombineWith' => 'OR' ] ),
				$this->newPropertyConstraints()
			)
		);
	}

	public function testGetFacetModeAndWhenSpecifiedByConstraintsAndAllowed(): void {
		$this->assertSame(
			ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_AND,
			$this->newListFacetHtmlBuilder()->getFacetMode(
				$this->newFacetConfig( [ 'defaultCombineWith' => 'OR', 'allowCombineWithChoice' => true ] ),
				$this->newPropertyConstraints()->withAdditionalAndValue( 'a' )->withAdditionalAndValue( 'b' )
			)
		);
	}

	public function testGetFacetModeOrWhenSpecifiedByConstraintsAndAllowed(): void {
		$this->assertSame(
			ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_OR,
			$this->newListFacetHtmlBuilder()->getFacetMode(
				$this->newFacetConfig( [ 'defaultCombineWith' => 'AND', 'allowCombineWithChoice' => true ] ),
				$this->newPropertyConstraints()->withOrValues( 'a', 'b' )
			)
		);
	}

	public function testGetFacetModeConfiguredDefaultOrWhenConstraintIsNotAllowed(): void {
		$this->assertSame(
			ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_OR,
			$this->newListFacetHtmlBuilder()->getFacetMode(
				$this->newFacetConfig( [ 'defaultCombineWith' => 'OR', 'allowCombineWithChoice' => false ] ),
				$this->newPropertyConstraints()->withAdditionalAndValue( 'a' )->withAdditionalAndValue( 'b' )
			)
		);
	}

	public function testGetFacetModeConfiguredDefaultAndWhenConstraintIsNotAllowed(): void {
		$this->assertSame(
			ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_AND,
			$this->newListFacetHtmlBuilder()->getFacetMode(
				$this->newFacetConfig( [ 'defaultCombineWith' => 'AND', 'allowCombineWithChoice' => false ] ),
				$this->newPropertyConstraints()->withOrValues( 'a', 'b' )
			)
		);
	}

	private function assertFacetMode( array $selectViewModel, string $selectedMode ): void {
		$this->assertSame( $selectedMode, $selectViewModel['defaultValue'] );
		foreach ( $selectViewModel['options'] as $option ) {
			if ( $option['value'] === $selectedMode ) {
				$this->assertTrue( $option['selected'] );
			} else {
				$this->assertFalse( $option['selected'] );
			}
		}
	}

	private function assertFacetModeHasOption( array $selectViewModel, string $optionValue ): void {
		foreach ( $selectViewModel['options'] as $option ) {
			if ( $option['value'] === $optionValue ) {
				$this->assertTrue( true );
				return;
			}
		}
		$this->fail( "Option $optionValue not found in select view model" );
	}

	private function assertFacetModeDoesNotHaveOption( array $selectViewModel, string $optionValue ): void {
		foreach ( $selectViewModel['options'] as $option ) {
			if ( $option['value'] === $optionValue ) {
				$this->fail( "Option $optionValue found in select view model" );
			}
		}
		$this->assertTrue( true );
	}

	public function testContainsAnyValueOption(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints()->requireAnyValue(),
			typeSpecificConfig: [ 'showAnyFilter' => true ]
		);

		$this->assertFacetModeHasOption( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_SHOW_ANY_FILTER );
	}

	public function testDoesNotContainAnyValueOption(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints()->requireAnyValue(),
			typeSpecificConfig: [ 'showAnyFilter' => false ]
		);

		$this->assertFacetModeDoesNotHaveOption( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_SHOW_ANY_FILTER );
	}

	public function testContainsNoValueOption(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints()->requireNoValue(),
			typeSpecificConfig: [ 'showNoneFilter' => true ]
		);

		$this->assertFacetModeHasOption( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_SHOW_NONE_FILTER );
	}

	public function testDoesNotContainNoValueOption(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints()->requireNoValue(),
			typeSpecificConfig: [ 'showNoneFilter' => false ]
		);

		$this->assertFacetModeDoesNotHaveOption( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_SHOW_NONE_FILTER );
	}

	public function testOrIsDisabledWhenAndIsSelectedAndChoiceIsDisabled(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints(),
			typeSpecificConfig: [ 'allowCombineWithChoice' => false, 'defaultCombineWith' => 'AND' ]
		);

		$this->assertFacetMode( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_AND );
		$this->assertFacetModeDoesNotHaveOption( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_OR );
	}

	public function testAndIsDisabledWhenOrIsSelectedAndChoiceIsDisabled(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints(),
			typeSpecificConfig: [ 'allowCombineWithChoice' => false, 'defaultCombineWith' => 'OR' ]
		);

		$this->assertFacetMode( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_OR );
		$this->assertFacetModeDoesNotHaveOption( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_AND );
	}

	public function testBothAndAndOrAreOptionsWhenChoiceIsAllowed(): void {
		$viewModel = $this->buildViewModel(
			constraints: $this->newPropertyConstraints(),
			typeSpecificConfig: [ 'allowCombineWithChoice' => true ]
		);

		$this->assertFacetModeHasOption( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_AND );
		$this->assertFacetModeHasOption( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_OR );
	}

	public function testAndIsSelectedWhenDefault(): void {
		$viewModel = $this->buildViewModel(
			typeSpecificConfig: [ 'defaultCombineWith' => 'AND' ]
		);

		$this->assertFacetMode( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_AND );
	}

	public function testOrIsSelectedWhenDefault(): void {
		$viewModel = $this->buildViewModel(
			typeSpecificConfig: [ 'defaultCombineWith' => 'OR' ]
		);

		$this->assertFacetMode( $viewModel['select'], ListFacetHtmlBuilder::CONFIG_VALUE_COMBINE_WITH_OR );
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

	public function testRendersTemplateWhenNoValuesExistAndAnyValueOptionSelected(): void {
		$html = $this->newListFacetHtmlBuilder(
			valueCounter: new SequentialValueCounter( 0 )
		)->buildHtml(
			config: $this->newFacetConfig(
				typeSpecificConfig: [ 'showAnyFilter' => true ]
			),
			state: $this->newPropertyConstraints()->requireAnyValue()
		);

		$this->assertStringContainsString( 'data-default-value="' . ListFacetHtmlBuilder::CONFIG_VALUE_SHOW_ANY_FILTER . '"', $html );
	}

	public function testRendersTemplateWhenNoValuesExistAndNoValueOptionSelected(): void {
		$html = $this->newListFacetHtmlBuilder(
			valueCounter: new SequentialValueCounter( 0 )
		)->buildHtml(
			config: $this->newFacetConfig(
				typeSpecificConfig: [ 'showNoneFilter' => true ]
			),
			state: $this->newPropertyConstraints()->requireNoValue()
		);

		$this->assertStringContainsString( 'data-default-value="' . ListFacetHtmlBuilder::CONFIG_VALUE_SHOW_NONE_FILTER . '"', $html );
	}

}
