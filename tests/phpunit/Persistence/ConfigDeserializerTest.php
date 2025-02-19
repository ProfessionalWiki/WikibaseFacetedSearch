<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigDeserializer;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\Valid;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigDeserializer
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 */
class ConfigDeserializerTest extends TestCase {

	public function testValidJsonReturnsConfig(): void {
		$deserializer = $this->newDeserializer();

		$this->assertEquals(
			Valid::config(),
			$deserializer->deserialize( Valid::configJson() )
		);
	}

	private function newDeserializer(): ConfigDeserializer {
		return WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer();
	}

	public function testInvalidJsonReturnsEmptyConfig(): void {
		$deserializer = $this->newDeserializer();

		$config = $deserializer->deserialize( '}{' );

		$this->assertEquals( new Config(), $config );
	}

	public function testInvalidItemTypePropertyReturnsEmptyConfig(): void {
		$deserializer = $this->newDeserializer();

		$config = $deserializer->deserialize( '{ "itemTypeProperty": "Q123" }' );

		$this->assertEquals( new Config(), $config );
	}

	public function testInvalidFacetsReturnsEmptyConfig(): void {
		$deserializer = $this->newDeserializer();

		$config = $deserializer->deserialize( '{ "configPerItemType": "foo" }' );

		$this->assertEquals( new Config(), $config );
	}

	public function testInvalidFacetConfigReturnsEmptyConfig(): void {
		$deserializer = $this->newDeserializer();

		$config = $deserializer->deserialize( '
{
	"configPerItemType": {
		"Q1": "notAnArray"
	}
}
		' );

		$this->assertEquals( new Config(), $config );
	}

	public function testCanDeserializeExampleConfig(): void {
		$this->assertCanDeserialize( file_get_contents( WikibaseFacetedSearchExtension::getInstance()->getExampleConfigPath() ) );
	}

	private function assertCanDeserialize( string $configJson ): void {
		$deserializer = $this->newDeserializer();

		$this->assertNotEquals(
			new Config(),
			$deserializer->deserialize( $configJson )
		);
	}

	public function testDeserializesTypeSpecificConfig(): void {
		$deserializer = $this->newDeserializer();

		$config = $deserializer->deserialize( '{
	"configPerItemType": {
		"Q200": {
			"facets": {
				"P2": {
					"type": "list"
				},
				"P3": {
					"type": "list",
					"defaultCombineWith": "OR",
					"allowCombineWithChoice": true,
					"showNoneFilter": true,
					"showAnyFilter": true
				},
				"P4": {
					"type": "list",
					"defaultCombineWith": "AND"
				}
			}
		}
	}
}
' );

		$this->assertSame(
			[
				'defaultCombineWith' => 'OR',
				'allowCombineWithChoice' => true,
				'showNoneFilter' => true,
				'showAnyFilter' => true,
			],
			$config->getConfigForProperty( new ItemId( 'Q200' ), new NumericPropertyId( 'P3' ) )->typeSpecificConfig
		);
	}

	public function testDefaultsToEmptyTypeSpecificConfig(): void {
		$deserializer = $this->newDeserializer();

		$config = $deserializer->deserialize( '{
	"configPerItemType": {
		"Q200": {
			"facets": {
				"P2": {
					"type": "list"
				},
				"P4": {
					"type": "list",
					"defaultCombineWith": "AND"
				}
			}
		}
	}
}
' );

		$this->assertSame(
			[],
			$config->getConfigForProperty( new ItemId( 'Q200' ), new NumericPropertyId( 'P2' ) )->typeSpecificConfig
		);
	}

}
