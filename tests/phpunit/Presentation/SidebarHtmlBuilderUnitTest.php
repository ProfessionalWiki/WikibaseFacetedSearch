<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\SidebarHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpyFacetHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpyTemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubQueryStringParser;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\SidebarHtmlBuilder
 */
class SidebarHtmlBuilderUnitTest extends TestCase {

	private function newSidebarHtmlBuilder(
		?Config $config = null,
		?SpyTemplateParser $templateSpy = null,
		?QueryStringParser $queryStringParser = null
	): SidebarHtmlBuilder {
		return new SidebarHtmlBuilder(
			$config ?? new Config(),
			new SpyFacetHtmlBuilder(),
			new StubLabelLookup( null ),
			$templateSpy ?? new SpyTemplateParser(),
			$queryStringParser ?? new StubQueryStringParser()
		);
	}

	public function testRendersEmptyStringWhenThereAreNoFacets(): void {
		$this->assertStringContainsString(
			'',
			$this->newSidebarHtmlBuilder()->createHtml( '' )
		);
	}

	//public function testRendersTemplate(): void {
	//	$this->assertStringContainsString(
	//		'wikibase-faceted-search__sidebar',
	//		$this->newSidebarHtmlBuilder(
	//			queryStringParser: new StubQueryStringParser(
	//				query: new Query( new PropertyConstraintsList() )
	//			)
	//		)->createHtml( '' )
	//	);
	//}

	// TODO: tests

}
