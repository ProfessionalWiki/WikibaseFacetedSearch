<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\CombiningConfigLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubConfigLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\WikibaseFacetedSearchIntegrationTest;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\CombiningConfigLookup
 */
class CombiningConfigLookupTest extends WikibaseFacetedSearchIntegrationTest {

	private function newLookup( string $baseConfig, Config $wikiConfig, bool $wikiConfigIsEnabled ): CombiningConfigLookup {
		return new CombiningConfigLookup(
			baseConfig: $baseConfig,
			deserializer: WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer(),
			configLookup: new StubConfigLookup( $wikiConfig ),
			wikiConfigIsEnabled: $wikiConfigIsEnabled
		);
	}

	public function testWikiConfigSupersedesBaseConfig(): void {
		$lookup = $this->newLookup(
			baseConfig: '{ "sitelinkSiteId": "enwiki" }',
			wikiConfig: new Config( sitelinkSiteId: 'dewiki' ),
			wikiConfigIsEnabled: true
		);

		$this->assertSame(
			'dewiki',
			$lookup->getConfig()->sitelinkSiteId
		);
	}

	public function testUsesBaseConfigIfThereIsNoWikiConfig(): void {
		$lookup = $this->newLookup(
			baseConfig: '{ "sitelinkSiteId": "enwiki" }',
			wikiConfig: new Config(),
			wikiConfigIsEnabled: true
		);

		$this->assertSame(
			'enwiki',
			$lookup->getConfig()->sitelinkSiteId
		);
	}

	public function testOnlyUsesWikiConfigWhenEnabled(): void {
		$lookup = $this->newLookup(
			baseConfig: '{ "sitelinkSiteId": "enwiki" }',
			wikiConfig: new Config( sitelinkSiteId: 'dewiki' ),
			wikiConfigIsEnabled: false
		);

		$this->assertSame(
			'enwiki',
			$lookup->getConfig()->sitelinkSiteId
		);
	}

}
