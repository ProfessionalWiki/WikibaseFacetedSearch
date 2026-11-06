<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use RuntimeException;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\Config
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList
 */
class ConfigTest extends TestCase {

	private function createOriginalConfig(): Config {
		return new Config(
			sitelinkSiteId: 'enwiki'
		);
	}

	private function createNewConfig(): Config {
		return new Config(
			sitelinkSiteId: 'dewiki'
		);
	}

	public function testOriginalValuesAreKeptWhenCombined(): void {
		$original = $this->createOriginalConfig();
		$new = new Config();

		$combined = $original->combine( $new );

		$this->assertEquals( $original, $combined );
	}

	public function testOriginalValuesAreReplacedWhenCombined(): void {
		$original = $this->createOriginalConfig();
		$new = $this->createNewConfig();

		$combined = $original->combine( $new );

		$this->assertEquals( $new, $combined );
	}

	public function testGetItemTypePropertyThrowsExceptionWhenNotConfigured(): void {
		$this->expectException( RuntimeException::class );
		( new Config() )->getItemTypeProperty();
	}

	public function testGetItemTypePropertyReturnsConfiguredId(): void {
		$propertyId = new NumericPropertyId( 'P42' );
		$config = new Config( itemTypeProperty: $propertyId );

		$this->assertSame( $propertyId, $config->getItemTypeProperty() );
	}

	public function testGetFacetsReturnsEmptyListByDefault(): void {
		$this->assertEquals(
			new FacetConfigList(),
			( new Config() )->getFacets()
		);
	}

	public function testGetFacetsReturnsConfiguredList(): void {
		$facets = new FacetConfigList();
		$config = new Config( facets: $facets );

		$this->assertSame( $facets, $config->getFacets() );
	}

	public function testGetConfigForPropertyReturnsNullOnNotFound(): void {
		$this->assertNull(
			( new Config() )->getConfigForProperty( new ItemId( 'Q404' ), new NumericPropertyId( 'P404' ) )
		);
	}

	public function testGetConfigForPropertyReturnsConfigForTheRightItemAndPropertyIdCombo(): void {
		$twoTwoHundredFacet = new FacetConfig(
			itemType: new ItemId( 'Q2' ),
			propertyId: new NumericPropertyId( 'P200' ),
			type: FacetType::RANGE
		);

		$config = new Config(
			facets: new FacetConfigList(
				new FacetConfig(
					itemType: new ItemId( 'Q1' ),
					propertyId: new NumericPropertyId( 'P200' ),
					type: FacetType::RANGE
				),
				new FacetConfig(
					itemType: new ItemId( 'Q2' ),
					propertyId: new NumericPropertyId( 'P100' ),
					type: FacetType::RANGE
				),
				$twoTwoHundredFacet,
				new FacetConfig(
					itemType: new ItemId( 'Q3' ),
					propertyId: new NumericPropertyId( 'P200' ),
					type: FacetType::RANGE
				),
				new FacetConfig(
					itemType: new ItemId( 'Q2' ),
					propertyId: new NumericPropertyId( 'P300' ),
					type: FacetType::RANGE
				)
			)
		);

		$this->assertEquals(
			$twoTwoHundredFacet,
			$config->getConfigForProperty( new ItemId( 'Q2' ), new NumericPropertyId( 'P200' ) )
		);
	}

	public function testGetItemTypes(): void {
		$config = new Config(
			facets: new FacetConfigList(
				new FacetConfig( new ItemId( 'Q1' ), new NumericPropertyId( 'P1' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q2' ), new NumericPropertyId( 'P2' ), FacetType::RANGE ),
				new FacetConfig( new ItemId( 'Q1' ), new NumericPropertyId( 'P3' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q3' ), new NumericPropertyId( 'P4' ), FacetType::RANGE )
			)
		);

		$this->assertEquals(
			[
				new ItemId( 'Q1' ),
				new ItemId( 'Q2' ),
				new ItemId( 'Q3' )
			],
			$config->getItemTypes()
		);
	}

	public function testWithoutItemTypeIsNotComplete(): void {
		$this->assertFalse( ( new Config() )->isComplete() );
	}

	public function testWithOnlyItemTypeIsComplete(): void {
		$config = new Config( itemTypeProperty: new NumericPropertyId( 'P1' ) );

		$this->assertTrue( $config->isComplete() );
	}

	public function testFullConfigIsComplete(): void {
		$config = new Config(
			sitelinkSiteId: 'enwiki',
			itemTypeProperty: new NumericPropertyId( 'P1' ),
			facets: new FacetConfigList(
				new FacetConfig( new ItemId( 'Q1' ), new NumericPropertyId( 'P1' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q2' ), new NumericPropertyId( 'P2' ), FacetType::LIST )
			)
		);

		$this->assertTrue( $config->isComplete() );
	}

	public function testGetIconForItemType(): void {
		$q1 = new ItemId( 'Q1' );
		$q2 = new ItemId( 'Q2' );
		$q3 = new ItemId( 'Q3' );

		$config = new Config(
			icons: [
				'Q1' => 'star',
				'Q2' => 'circle',
			]
		);

		$this->assertSame( 'star', $config->getIconForItemType( $q1 ) );
		$this->assertSame( 'circle', $config->getIconForItemType( $q2 ) );
		$this->assertNull( $config->getIconForItemType( $q3 ) );
	}

	public function testGetPropertiesWithFacetsForItemType(): void {
		$config = new Config(
			sitelinkSiteId: 'enwiki',
			itemTypeProperty: new NumericPropertyId( 'P42' ),
			facets: new FacetConfigList(
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P2' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q200' ), new NumericPropertyId( 'P3' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P4' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q200' ), new NumericPropertyId( 'P5' ), FacetType::LIST ),
			)
		);

		$this->assertEquals(
			[
				new NumericPropertyId( 'P2' ),
				new NumericPropertyId( 'P4' ),
			],
			$config->getPropertiesWithFacetsForItemType( new ItemId( 'Q100' ) )
		);
	}

}
