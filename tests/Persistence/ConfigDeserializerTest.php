<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\Valid;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigDeserializer
 */
class ConfigDeserializerTest extends TestCase {

	public function testValidJsonReturnsConfig(): void {
		$deserializer = WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer();

		$this->assertEquals(
			Valid::config(),
			$deserializer->deserialize( Valid::configJson() )
		);
	}

	public function testInvalidJsonReturnsEmptyConfig(): void {
		$deserializer = WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer();

		$config = $deserializer->deserialize( '}{' );

		$this->assertEquals( new Config(), $config );
	}

	public function testInvalidInstanceOfIdReturnsEmptyConfig(): void {
		$deserializer = WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer();

		$config = $deserializer->deserialize( '{ "instanceOfId": "Q123" }' );

		$this->assertEquals( new Config(), $config );
	}

	public function testInvalidFacetsReturnsEmptyConfig(): void {
		$deserializer = WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer();

		$config = $deserializer->deserialize( '{ "facets": "foo" }' );

		$this->assertEquals( new Config(), $config );
	}

	public function testInvalidFacetConfigReturnsEmptyConfig(): void {
		$deserializer = WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer();

		$config = $deserializer->deserialize( '
{
	"facets": {
		"Q1": "notAnArray"
	}
}
		' );

		$this->assertEquals( new Config(), $config );
	}

}
