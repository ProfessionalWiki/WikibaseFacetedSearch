<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use MediaWiki\MediaWikiServices;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\UiBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use RuntimeException;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\UiBuilder
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 */
class UiBuilderIntegrationTest extends TestCase {

	public function testIntegrationSmoke(): void {
		try {
			WikibaseFacetedSearchExtension::getInstance()->getConfig()->getInstanceOfId();
		} catch ( RuntimeException ) {
			$this->markTestSkipped( 'No valid config available' );
		}

		$html = $this->getUiBuilderFromGlobals()->createHtml( 'foo' );
		$this->assertStringContainsString( 'topbar', $html );
		$this->assertStringContainsString( 'sidebar', $html );
	}

	private function getUiBuilderFromGlobals(): UiBuilder {
		return WikibaseFacetedSearchExtension::getInstance()->getUiBuilder( MediaWikiServices::getInstance()->getContentLanguage() );
	}

}
