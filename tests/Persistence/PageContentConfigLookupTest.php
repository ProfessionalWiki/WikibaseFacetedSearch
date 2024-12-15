<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\Valid;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\WikibaseFacetedSearchIntegrationTest;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @group Database
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageContentConfigLookup
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageContentFetcher
 */
class PageContentConfigLookupTest extends WikibaseFacetedSearchIntegrationTest {

	public function tearDown(): void {
		$this->deleteConfigPage();
		parent::tearDown();
	}

	public function testEmptyPageConfig(): void {
		$this->editConfigPage( '{}' );
		$lookup = WikibaseFacetedSearchExtension::getInstance()->newPageContentConfigLookup();

		$config = $lookup->getConfig();
		$emptyConfig = new Config();

		$this->assertEquals( $emptyConfig, $config );
	}

	public function testSavedPageConfig(): void {
		$this->editConfigPage( Valid::configJson() );
		$lookup = WikibaseFacetedSearchExtension::getInstance()->newPageContentConfigLookup();

		$config = $lookup->getConfig();

		$this->assertEquals(
			Valid::config(),
			$config
		);
	}

}
