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

	private function newLookup( string $baseConfig, Config $wikiConfig, bool $enableWikiConfig ): CombiningConfigLookup {
		return new CombiningConfigLookup(
			baseConfig: $baseConfig,
			deserializer: WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer(),
			configLookup: new StubConfigLookup( $wikiConfig ),
			enableWikiConfig: $enableWikiConfig
		);
	}

	public function testWikiConfigSupersedesBaseConfig(): void {
		$lookup = $this->newLookup(
			baseConfig: '{ "linkTargetSitelinkSiteId": "enwiki" }',
			wikiConfig: new Config( linkTargetSitelinkSiteId: 'dewiki' ),
			enableWikiConfig: true
		);

		$this->assertSame(
			'dewiki',
			$lookup->getConfig()->linkTargetSitelinkSiteId
		);
	}

	public function testUsesBaseConfigIfThereIsNoWikiConfig(): void {
		$lookup = $this->newLookup(
			baseConfig: '{ "linkTargetSitelinkSiteId": "enwiki" }',
			wikiConfig: new Config(),
			enableWikiConfig: true
		);

		$this->assertSame(
			'enwiki',
			$lookup->getConfig()->linkTargetSitelinkSiteId
		);
	}

	public function testOnlyUsesWikiConfigWhenEnabled(): void {
		$lookup = $this->newLookup(
			baseConfig: '{ "linkTargetSitelinkSiteId": "enwiki" }',
			wikiConfig: new Config( linkTargetSitelinkSiteId: 'dewiki' ),
			enableWikiConfig: false
		);

		$this->assertSame(
			'enwiki',
			$lookup->getConfig()->linkTargetSitelinkSiteId
		);
	}

}
