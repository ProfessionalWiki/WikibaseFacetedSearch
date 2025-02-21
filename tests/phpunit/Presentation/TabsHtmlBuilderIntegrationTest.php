<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\TabsHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\TabsHtmlBuilder
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 * @group Database
 */
class TabsHtmlBuilderIntegrationTest extends MediaWikiIntegrationTestCase {

	public function testIntegrationSmoke(): void {
		$this->overrideConfigValue(
			WikibaseFacetedSearchExtension::CONFIG_VARIABLE_NAME,
			'{"itemTypeProperty":"P31"}'
		);

		$html = $this->getTabsHtmlBuilderFromGlobals()->createHtml( 'foo' );
		$this->assertStringContainsString( 'topbar', $html );
	}

	private function getTabsHtmlBuilderFromGlobals(): TabsHtmlBuilder {
		$services = MediaWikiServices::getInstance();
		return WikibaseFacetedSearchExtension::getInstance()->getTabsHtmlBuilder(
			language: $services->getContentLanguage(),
			user: $services->getUserFactory()->newFromName( 'Test' )
		);
	}

}
