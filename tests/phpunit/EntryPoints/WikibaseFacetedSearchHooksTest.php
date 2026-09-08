<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\EntryPoints;

use CirrusSearch\Search\EmptySearchResultSet;
use Elastica\Query\MatchAll;
use FauxSearchResultSet;
use ISearchResultSet;
use MediaWiki\Context\RequestContext;
use MediaWiki\MainConfigNames;
use MediaWiki\Output\OutputPage;
use MediaWiki\Specials\SpecialSearch;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseFacetedSearch\EntryPoints\WikibaseFacetedSearchHooks;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubCirrusSearchResultSet;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\Valid;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\EntryPoints\WikibaseFacetedSearchHooks
 * @group Database
 */
class WikibaseFacetedSearchHooksTest extends MediaWikiIntegrationTestCase {

	private const SEARCH = 'haswbfacet:P42=' . Valid::ITEM_TYPE_WITH_FACETS;

	protected function setUp(): void {
		parent::setUp();

		unset( $GLOBALS[WikibaseFacetedSearchExtension::QUERY_GLOBAL] );

		$this->overrideConfigValue( 'WikibaseFacetedSearchEnableInWikiConfig', false );
		$this->overrideConfigValue( WikibaseFacetedSearchExtension::CONFIG_VARIABLE_NAME, Valid::configJson() );
		WikibaseFacetedSearchExtension::getInstance()->clearConfig();

		// The test database prefix ends up in the index name derived from the wiki id, for which no index exists.
		$this->overrideConfigValue( 'CirrusSearchIndexBaseName', $this->getConfVar( MainConfigNames::DBname ) );
	}

	protected function tearDown(): void {
		unset( $GLOBALS[WikibaseFacetedSearchExtension::QUERY_GLOBAL] );
		WikibaseFacetedSearchExtension::getInstance()->clearConfig();

		parent::tearDown();
	}

	public function testNoSidebarIsShownWhenTheSearchFailed(): void {
		$this->assertSame( '', $this->getSidebarHtmlFor( null ) );
	}

	public function testNoSidebarIsShownWhenAnotherSearchEngineAnswered(): void {
		$this->assertSame( '', $this->getSidebarHtmlFor( new FauxSearchResultSet( [] ) ) );
	}

	public function testNoSidebarIsShownWhenTheSearchYieldsNoElasticsearchQuery(): void {
		$this->assertSame( '', $this->getSidebarHtmlFor( new EmptySearchResultSet( false ) ) );
	}

	public function testSidebarIsShownWhenTheSearchYieldsAnElasticsearchQuery(): void {
		$this->assertStringContainsString(
			'wikibase-faceted-search__sidebar',
			$this->getSidebarHtmlFor( new StubCirrusSearchResultSet( new MatchAll() ) )
		);
	}

	private function getSidebarHtmlFor( ?ISearchResultSet $textMatches ): string {
		WikibaseFacetedSearchHooks::onSpecialSearchResults( self::SEARCH, null, $textMatches );

		$output = new OutputPage( RequestContext::getMain() );
		WikibaseFacetedSearchHooks::onSpecialSearchResultsAppend( $this->newSpecialSearch(), $output, self::SEARCH );

		return $output->getHTML();
	}

	private function newSpecialSearch(): SpecialSearch {
		/** @var SpecialSearch $specialSearch */
		$specialSearch = $this->getServiceContainer()->getSpecialPageFactory()->getPage( 'Search' );
		$specialSearch->setContext( RequestContext::getMain() );
		return $specialSearch;
	}

}
