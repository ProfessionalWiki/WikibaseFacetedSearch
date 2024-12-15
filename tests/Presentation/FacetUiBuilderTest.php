<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetUiBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\Valid;
use TemplateParser;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetUiBuilder
 */
class FacetUiBuilderTest extends TestCase {

	public function testRendersWrapper(): void {
		$html = $this->newFacetUBuilder()->createHtml();

		$this->assertStringContainsString( '<div class="wikibase-faceted-search__facets">', $html );
	}

	private function newFacetUBuilder(): FacetUiBuilder {
		return new FacetUiBuilder(
			parser: $this->newTemplateParser(),
			config: Valid::config()
		);
	}

	private function newTemplateParser(): TemplateParser {
		return new TemplateParser( __DIR__ . '/../../templates' );
	}

}
