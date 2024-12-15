<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList;
use RuntimeException;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\Config
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

}
