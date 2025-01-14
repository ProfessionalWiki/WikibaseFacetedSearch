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
			linkTargetSitelinkSiteId: 'enwiki'
		);
	}

	private function createNewConfig(): Config {
		return new Config(
			linkTargetSitelinkSiteId: 'dewiki'
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

	public function testGetInstanceOfIdThrowsExceptionWhenNotConfigured(): void {
		$this->expectException( RuntimeException::class );
		( new Config() )->getInstanceOfId();
	}

	public function testGetInstanceOfIdReturnsConfiguredId(): void {
		$propertyId = new NumericPropertyId( 'P42' );
		$config = new Config( instanceOfId: $propertyId );

		$this->assertSame( $propertyId, $config->getInstanceOfId() );
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
			instanceTypeId: new ItemId( 'Q2' ),
			propertyId: new NumericPropertyId( 'P200' ),
			type: FacetType::RANGE
		);

		$config = new Config(
			facets: new FacetConfigList(
				new FacetConfig(
					instanceTypeId: new ItemId( 'Q1' ),
					propertyId: new NumericPropertyId( 'P200' ),
					type: FacetType::RANGE
				),
				new FacetConfig(
					instanceTypeId: new ItemId( 'Q2' ),
					propertyId: new NumericPropertyId( 'P100' ),
					type: FacetType::RANGE
				),
				$twoTwoHundredFacet,
				new FacetConfig(
					instanceTypeId: new ItemId( 'Q3' ),
					propertyId: new NumericPropertyId( 'P200' ),
					type: FacetType::RANGE
				),
				new FacetConfig(
					instanceTypeId: new ItemId( 'Q2' ),
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

}
