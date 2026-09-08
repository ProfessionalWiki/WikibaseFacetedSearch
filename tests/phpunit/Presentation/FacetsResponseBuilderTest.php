<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraintsList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCount;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounter;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetsResponseBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetValueFormatter;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpyValueCounter;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubPropertyDataTypeLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\Valid;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Services\Lookup\LabelLookup;
use Wikibase\DataModel\Term\Term;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetsResponseBuilder
 */
class FacetsResponseBuilderTest extends TestCase {

	private function newBuilder(
		?Config $config = null,
		?LabelLookup $labelLookup = null,
		?ValueCounter $valueCounter = null,
		?FacetValueFormatter $valueFormatter = null
	): FacetsResponseBuilder {
		return new FacetsResponseBuilder(
			$config ?? Valid::config(),
			$labelLookup ?? new StubLabelLookup( null ),
			$valueCounter ?? new SpyValueCounter(),
			$valueFormatter ?? $this->newValueFormatter()
		);
	}

	private function newValueFormatter( ?string $dataType = null, ?Term $label = null ): FacetValueFormatter {
		return new FacetValueFormatter(
			new StubPropertyDataTypeLookup( $dataType ),
			new StubLabelLookup( $label )
		);
	}

	private function newConfigWithFacets( FacetConfig ...$facets ): Config {
		return new Config(
			itemTypeProperty: new NumericPropertyId( 'P42' ),
			facets: new FacetConfigList( ...$facets )
		);
	}

	private function newQuery( string ...$itemTypes ): Query {
		return new Query(
			new PropertyConstraintsList(),
			itemTypes: array_map( fn( string $itemType ) => new ItemId( $itemType ), $itemTypes )
		);
	}

	public function testListFacetHasValuesWithCountsAndLabels(): void {
		$facets = $this->newBuilder(
			labelLookup: new StubLabelLookup( new Term( 'en', 'country' ) ),
			valueCounter: new SpyValueCounter( new ValueCount( 'Q2', 3 ), new ValueCount( 'Q3', 1 ) ),
			valueFormatter: $this->newValueFormatter( 'wikibase-item', new Term( 'en', 'United States' ) )
		)->buildFacets( $this->newQuery( Valid::ITEM_TYPE_WITH_FACETS ) );

		$this->assertSame(
			[
				'property' => 'P1',
				'label' => 'country',
				'type' => 'list',
				'values' => [
					[ 'value' => 'Q2', 'count' => 3, 'label' => 'United States' ],
					[ 'value' => 'Q3', 'count' => 1, 'label' => 'United States' ]
				]
			],
			$facets[0]
		);
	}

	public function testRangeFacetHasNoValues(): void {
		$facets = $this->newBuilder(
			valueCounter: new SpyValueCounter( new ValueCount( 'Q2', 3 ) )
		)->buildFacets( $this->newQuery( Valid::ITEM_TYPE_WITH_FACETS ) );

		$this->assertSame( 'range', $facets[1]['type'] );
		$this->assertSame( [], $facets[1]['values'] );
	}

	public function testRangeFacetsAreNotCounted(): void {
		$valueCounter = new SpyValueCounter();

		$this->newBuilder( valueCounter: $valueCounter )
			->buildFacets( $this->newQuery( Valid::ITEM_TYPE_WITH_FACETS ) );

		$this->assertSame( [ 'P1' ], $valueCounter->getRequestedPropertyIds() );
	}

	public function testFacetsAreInConfigurationOrder(): void {
		$facets = $this->newBuilder(
			config: $this->newConfigWithFacets(
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P3' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P1' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P2' ), FacetType::LIST )
			)
		)->buildFacets( $this->newQuery( 'Q100' ) );

		$this->assertSame( [ 'P3', 'P1', 'P2' ], array_column( $facets, 'property' ) );
	}

	public function testFirstItemTypeInTheQuerySelectsTheConfiguration(): void {
		$facets = $this->newBuilder(
			config: $this->newConfigWithFacets(
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P1' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q200' ), new NumericPropertyId( 'P2' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q300' ), new NumericPropertyId( 'P3' ), FacetType::LIST )
			)
		)->buildFacets( $this->newQuery( 'Q200', 'Q300' ) );

		$this->assertSame( [ 'P2' ], array_column( $facets, 'property' ) );
	}

	public function testConstraintsForThePropertyAreCounted(): void {
		$valueCounter = new SpyValueCounter();

		$this->newBuilder( valueCounter: $valueCounter )->buildFacets(
			new Query(
				new PropertyConstraintsList(
					( new PropertyConstraints( new NumericPropertyId( 'P1' ) ) )->withOrValues( 'Q2', 'Q3' )
				),
				itemTypes: [ new ItemId( Valid::ITEM_TYPE_WITH_FACETS ) ]
			)
		);

		$this->assertSame(
			[ 'Q2', 'Q3' ],
			$valueCounter->getRequestedConstraints()[0]->getOrSelectedValues()
		);
	}

	public function testUnconfiguredItemTypeHasNoFacets(): void {
		$this->assertSame(
			[],
			$this->newBuilder()->buildFacets( $this->newQuery( 'Q404' ) )
		);
	}

	public function testQueryWithoutItemTypeHasNoFacets(): void {
		$this->assertSame(
			[],
			$this->newBuilder()->buildFacets( $this->newQuery() )
		);
	}

	public function testFacetLabelFallsBackToThePropertyId(): void {
		$facets = $this->newBuilder( labelLookup: new StubLabelLookup( null ) )
			->buildFacets( $this->newQuery( Valid::ITEM_TYPE_WITH_FACETS ) );

		$this->assertSame( 'P1', $facets[0]['label'] );
	}

	public function testValueLabelFallsBackToTheValue(): void {
		$facets = $this->newBuilder(
			valueCounter: new SpyValueCounter( new ValueCount( 42, 7 ) ),
			valueFormatter: $this->newValueFormatter( 'quantity', new Term( 'en', 'Only used for items' ) )
		)->buildFacets( $this->newQuery( Valid::ITEM_TYPE_WITH_FACETS ) );

		$this->assertSame(
			[ [ 'value' => 42, 'count' => 7, 'label' => '42' ] ],
			$facets[0]['values']
		);
	}

}
