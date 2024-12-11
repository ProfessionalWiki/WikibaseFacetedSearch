<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetUiBuilder;
use TemplateParser;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetUiBuilder
 */
class FacetUiBuilderTest extends TestCase {

	public function testRendersWrapper(): void {
		$builder = new FacetUiBuilder( $this->newTemplateParser() );

		$html = $builder->createHtml();

		$this->assertStringContainsString( '<div class="wikibase-faceted-search__facets">', $html );
	}

	private function newTemplateParser(): TemplateParser {
		return new TemplateParser( __DIR__ . '/../../templates' );
	}

}
