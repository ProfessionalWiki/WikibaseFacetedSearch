<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig
 */
class FacetConfigListTest extends TestCase {

	public function testFacetConfigCanBeRetrieved(): void {
		$itemId = new ItemId( 'Q1' );
		$facetConfig = new FacetConfig( $itemId, new NumericPropertyId( 'P1' ), FacetType::LIST );

		$facetConfigList = new FacetConfigList( $facetConfig );

		$this->assertSame(
			[ $facetConfig ],
			$facetConfigList->getFacetConfigForInstanceType( $itemId )
		);
	}

	public function testCanAddMultipleFacetConfigsWithTheSameItemId(): void {
		$itemId1 = new ItemId( 'Q1' );
		$facetConfig1 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), FacetType::LIST );
		$facetConfig2 = new FacetConfig( $itemId1, new NumericPropertyId( 'P2' ), FacetType::LIST );

		$facetConfigList = new FacetConfigList(
			$facetConfig1,
			$facetConfig2
		);

		$this->assertSame(
			[ $facetConfig1, $facetConfig2 ],
			$facetConfigList->getFacetConfigForInstanceType( $itemId1 )
		);
	}

	public function testCanAddMultipleFacetConfigsWithTheSamePropertyIdForOneItemId(): void {
		$itemId1 = new ItemId( 'Q1' );
		$facetConfig1 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), FacetType::BOOLEAN );
		$facetConfig2 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), FacetType::LIST );

		$facetConfigList = new FacetConfigList(
			$facetConfig1,
			$facetConfig2
		);

		$this->assertSame(
			[ $facetConfig1, $facetConfig2 ],
			$facetConfigList->getFacetConfigForInstanceType( $itemId1 )
		);
	}

	public function testCanAddMultipleFacetConfigsWithTheSamePropertyIdForDifferentItemIds(): void {
		$itemId1 = new ItemId( 'Q1' );
		$itemId2 = new ItemId( 'Q2' );
		$facetConfig1 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), FacetType::BOOLEAN );
		$facetConfig2 = new FacetConfig( $itemId1, new NumericPropertyId( 'P2' ), FacetType::LIST );
		$facetConfig3 = new FacetConfig( $itemId2, new NumericPropertyId( 'P1' ), FacetType::BOOLEAN );
		$facetConfig4 = new FacetConfig( $itemId2, new NumericPropertyId( 'P2' ), FacetType::LIST );

		$facetConfigList = new FacetConfigList(
			$facetConfig1,
			$facetConfig2,
			$facetConfig3,
			$facetConfig4
		);

		$this->assertSame(
			[ $facetConfig1, $facetConfig2 ],
			$facetConfigList->getFacetConfigForInstanceType( $itemId1 )
		);

		$this->assertSame(
			[ $facetConfig3, $facetConfig4 ],
			$facetConfigList->getFacetConfigForInstanceType( $itemId2 )
		);
	}

	public function testFacetConfigsAreRetrievedInTheAddingOrder(): void {
		$itemId1 = new ItemId( 'Q1' );
		$itemId2 = new ItemId( 'Q2' );
		$facetConfig1 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), FacetType::BOOLEAN );
		$facetConfig2 = new FacetConfig( $itemId1, new NumericPropertyId( 'P1' ), FacetType::LIST );
		$facetConfig3 = new FacetConfig( $itemId1, new NumericPropertyId( 'P2' ), FacetType::RANGE );
		$facetConfig4 = new FacetConfig( $itemId2, new NumericPropertyId( 'P1' ), FacetType::BOOLEAN );
		$facetConfig5 = new FacetConfig( $itemId2, new NumericPropertyId( 'P1' ), FacetType::LIST );
		$facetConfig6 = new FacetConfig( $itemId2, new NumericPropertyId( 'P2' ), FacetType::RANGE );

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
			$facetConfigList->getFacetConfigForInstanceType( $itemId1 )
		);

		$this->assertSame(
			[ $facetConfig6, $facetConfig5, $facetConfig4 ],
			$facetConfigList->getFacetConfigForInstanceType( $itemId2 )
		);
	}

	public function testGettingFacetConfigForUnknownItemIdReturnsEmptyArray(): void {
		$this->assertSame(
			[],
			( new FacetConfigList() )->getFacetConfigForInstanceType( new ItemId( 'Q404' ) )
		);
	}

}
