<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\UiBuilder
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 */
class UiBuilderTest extends TestCase {

	public function testIntegrationSmoke(): void {
		$html = WikibaseFacetedSearchExtension::getInstance()->getUiBuilder()->createHtml( 'foo' );
		$this->assertStringContainsString( 'topbar', $html );
		$this->assertStringContainsString( 'sidebar', $html );
	}

}
