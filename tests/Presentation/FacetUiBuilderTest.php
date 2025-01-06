<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use MediaWiki\Html\TemplateParser;
use MediaWiki\MediaWikiServices;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetUiBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\Valid;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetUiBuilder
 */
class FacetUiBuilderTest extends TestCase {

	public function testRendersWrapper(): void {
		$html = $this->newFacetUBuilder(
			// TODO: Is there a better way to pass URL in a test?
			'https://example.com/w/index.php?search=test&title=Special%3ASearch&profile=default&fulltext=1&wbfs-Author-values=Alice'
		)->createHtml( new ItemId( Valid::ITEM_TYPE_WITH_FACETS ) );

		$this->assertStringContainsString( '<div class="wikibase-faceted-search__facets">', $html );
	}

	private function newFacetUBuilder( string $url ): FacetUiBuilder {
		return new FacetUiBuilder(
			parser: $this->newTemplateParser(),
			config: Valid::config(),
			url: $url,
			urlUtils: $this->geturlUtils()
		);
	}

	private function newTemplateParser(): TemplateParser {
		return new TemplateParser( __DIR__ . '/../../templates' );
	}

	private function geturlUtils(): UrlUtils {
		return MediaWikiServices::getInstance()->getUrlUtils();
	}

}
