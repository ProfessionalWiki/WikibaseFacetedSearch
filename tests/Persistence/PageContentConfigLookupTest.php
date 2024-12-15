<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\Valid;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\WikibaseFacetedSearchIntegrationTest;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageContentConfigLookup
 * @group Database
 */
class PageContentConfigLookupTest extends WikibaseFacetedSearchIntegrationTest {

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
