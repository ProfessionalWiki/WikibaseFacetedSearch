<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\UiBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\UiBuilder
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 * @group Database
 */
class UiBuilderIntegrationTest extends MediaWikiIntegrationTestCase {

	public function testIntegrationSmoke(): void {
		$this->overrideConfigValue(
			WikibaseFacetedSearchExtension::CONFIG_VARIABLE_NAME,
			'{"itemTypeProperty":"P31"}'
		);

		WikibaseFacetedSearchExtension::getInstance()->getConfig()->getItemTypeProperty();

		$html = $this->getUiBuilderFromGlobals()->createHtml( 'foo' );
		$this->assertStringContainsString( 'topbar', $html );
		$this->assertStringContainsString( 'sidebar', $html );
	}

	private function getUiBuilderFromGlobals(): UiBuilder {
		return WikibaseFacetedSearchExtension::getInstance()->getUiBuilder( MediaWikiServices::getInstance()->getContentLanguage() );
	}

}
