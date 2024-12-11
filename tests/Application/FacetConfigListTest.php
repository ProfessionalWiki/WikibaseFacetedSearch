<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList
 */
class FacetConfigListTest extends TestCase {

	public function testFacetConfigCanBeRetrieved(): void {
		$itemId = new ItemId( 'Q1' );
		$facetConfig = new FacetConfig( $itemId, new NumericPropertyId( 'P1' ), 'list' );

		$facetConfigList = new FacetConfigList( $facetConfig );

		$this->assertSame(
			[ $facetConfig ],
			$facetConfigList->getFacetConfigForItemId( $itemId )
		);
	}

	public function testCanAddMultipleFacetConfigsWithTheSameItemId(): void {
		$itemId1 = new ItemId( 'Q1' );
		$facetConfig1 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), 'list' );
		$facetConfig2 = new FacetConfig( $itemId1, new NumericPropertyId( 'P2' ), 'list' );

		$facetConfigList = new FacetConfigList(
			$facetConfig1,
			$facetConfig2
		);

		$this->assertSame(
			[ $facetConfig1, $facetConfig2 ],
			$facetConfigList->getFacetConfigForItemId( $itemId1 )
		);
	}

	public function testCanAddMultipleFacetConfigsWithTheSamePropertyIdForOneItemId(): void {
		$itemId1 = new ItemId( 'Q1' );
		$facetConfig1 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), 'boolean' );
		$facetConfig2 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), 'list' );

		$facetConfigList = new FacetConfigList(
			$facetConfig1,
			$facetConfig2
		);

		$this->assertSame(
			[ $facetConfig1, $facetConfig2 ],
			$facetConfigList->getFacetConfigForItemId( $itemId1 )
		);
	}

	public function testCanAddMultipleFacetConfigsWithTheSamePropertyIdForDifferentItemIds(): void {
		$itemId1 = new ItemId( 'Q1' );
		$itemId2 = new ItemId( 'Q2' );
		$facetConfig1 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), 'boolean' );
		$facetConfig2 = new FacetConfig( $itemId1, new NumericPropertyId( 'P2' ), 'list' );
		$facetConfig3 = new FacetConfig( $itemId2, new NumericPropertyId( 'P1' ), 'boolean' );
		$facetConfig4 = new FacetConfig( $itemId2, new NumericPropertyId( 'P2' ), 'list' );

		$facetConfigList = new FacetConfigList(
			$facetConfig1,
			$facetConfig2,
			$facetConfig3,
			$facetConfig4
		);

		$this->assertSame(
			[ $facetConfig1, $facetConfig2 ],
			$facetConfigList->getFacetConfigForItemId( $itemId1 )
		);

		$this->assertSame(
			[ $facetConfig3, $facetConfig4 ],
			$facetConfigList->getFacetConfigForItemId( $itemId2 )
		);
	}

	public function testFacetConfigsAreRetrievedInTheAddingOrder(): void {
		$itemId1 = new ItemId( 'Q1' );
		$itemId2 = new ItemId( 'Q2' );
		$facetConfig1 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), 'boolean' );
		$facetConfig2 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), 'list' );
		$facetConfig3 = new FacetConfig( $itemId1, new NumericPropertyId( 'P2' ), 'range' );
		$facetConfig4 = new FacetConfig( $itemId2, new NumericPropertyId( 'P1' ), 'boolean' );
		$facetConfig5 = new FacetConfig( $itemId2, new NumericPropertyId( 'P1' ), 'list' );
		$facetConfig6 = new FacetConfig( $itemId2, new NumericPropertyId( 'P2' ), 'range' );

		$facetConfigList = new FacetConfigList(
			$facetConfig2,
			$facetConfig6,
			$facetConfig3,
			$facetConfig5,
			$facetConfig1,
			$facetConfig4
		);

		$this->assertSame(
			[ $facetConfig2, $facetConfig3, $facetConfig1 ],
			$facetConfigList->getFacetConfigForItemId( $itemId1 )
		);

		$this->assertSame(
			[ $facetConfig6, $facetConfig5, $facetConfig4 ],
			$facetConfigList->getFacetConfigForItemId( $itemId2 )
		);
	}

	public function testGettingFacetConfigForUnknownItemIdReturnsEmptyArray(): void {
		$this->assertSame(
			[],
			( new FacetConfigList() )->getFacetConfigForItemId( new ItemId( 'Q404' ) )
		);
	}

}
