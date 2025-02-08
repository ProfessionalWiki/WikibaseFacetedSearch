<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraintsList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\SidebarHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\FakeItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpyFacetHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpyTemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubQueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\SidebarHtmlBuilder
 */
class SidebarHtmlBuilderUnitTest extends TestCase {

	// TODO: tests

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

}
