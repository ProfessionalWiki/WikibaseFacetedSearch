<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use MediaWiki\Html\TemplateParser;
use MediaWiki\MediaWikiServices;
use MediaWiki\Utils\UrlUtils;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetUiBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\Valid;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetUiBuilder
 */
class FacetUiBuilderTest extends TestCase {

	public function testRendersWrapper(): void {
		$html = $this->newFacetUBuilder()->createHtml(
			new ItemId( Valid::ITEM_TYPE_WITH_FACETS ),
			// TODO: Is there a better way to pass URL in a test?
			'https://example.com/w/index.php?search=test&title=Special%3ASearch&profile=default&fulltext=1&wbfs-Author-values=Alice'
		);

		$this->assertStringContainsString( '<div class="wikibase-faceted-search__facets">', $html );
	}

	private function newFacetUBuilder(): FacetUiBuilder {
		return new FacetUiBuilder(
			parser: $this->newTemplateParser(),
			queryStringParser: $this->newQueryStringParser(),
			config: Valid::config(),
			urlUtils: $this->geturlUtils()
		);
	}

	private function newTemplateParser(): TemplateParser {
		return new TemplateParser( __DIR__ . '/../../templates' );
	}

	private function newQueryStringParser(): QueryStringParser {
		return new QueryStringParser();
	}

	private function geturlUtils(): UrlUtils {
		return MediaWikiServices::getInstance()->getUrlUtils();
	}

}
