<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\Valid;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigDeserializer
 */
class ConfigDeserializerTest extends TestCase {

	public function testValidJsonReturnsConfig(): void {
		$deserializer = WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer();

		$config = $deserializer->deserialize( Valid::configJson() );

		$this->assertEquals(
			new Config(
				linkTargetSitelinkSiteId: 'enwiki'
			),
			$config
		);
	}

	public function testInvalidJsonReturnsEmptyConfig(): void {
		$deserializer = WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer();

		$config = $deserializer->deserialize( '}{' );
		$emptyConfig = new Config();

		$this->assertEquals( $emptyConfig, $config );
	}

}
