<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use Elastica\Query\MatchAll;
use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\SidebarHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\SidebarHtmlBuilder
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 * @group Database
 */
class SidebarHtmlBuilderIntegrationTest extends MediaWikiIntegrationTestCase {

	public function testIntegrationSmoke(): void {
		$this->overrideConfigValue(
			WikibaseFacetedSearchExtension::CONFIG_VARIABLE_NAME,
			<<<JSON
{
	"itemTypeProperty": "P42",
	"configPerItemType": {
		"Q1": {
			"facets": {
				"P100": {
					"type": "range"
				}
			}
		}
	}
}
JSON
		);

		$html = $this->getSidebarHtmlBuilderFromGlobals()->createHtml( 'foo haswbfacet:P42=Q1' );
		$this->assertStringContainsString( 'sidebar', $html );
	}

	private function getSidebarHtmlBuilderFromGlobals(): SidebarHtmlBuilder {
		return WikibaseFacetedSearchExtension::getInstance()->getSidebarHtmlBuilder(
			language: MediaWikiServices::getInstance()->getContentLanguage(),
			currentQuery: new MatchAll()
		);
	}

}
