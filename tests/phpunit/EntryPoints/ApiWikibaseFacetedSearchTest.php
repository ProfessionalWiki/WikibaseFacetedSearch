<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\EntryPoints;

use CirrusSearch\Search\EmptySearchResultSet;
use Elastica\Query\MatchAll;
use MediaWiki\Status\Status;
use MediaWiki\Tests\Api\ApiTestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubCirrusSearchResultSet;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpySearchEngine;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubSearchEngineFactory;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\Valid;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use SearchEngine;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\EntryPoints\ApiWikibaseFacetedSearch
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 * @group Database
 */
class ApiWikibaseFacetedSearchTest extends ApiTestCase {

	private const SEARCH = 'haswbfacet:P42=' . Valid::ITEM_TYPE_WITH_FACETS;

	protected function setUp(): void {
		parent::setUp();

		$this->overrideConfigValue( 'WikibaseFacetedSearchEnableInWikiConfig', false );
		$this->useConfig( Valid::configJson() );
	}

	protected function tearDown(): void {
		WikibaseFacetedSearchExtension::getInstance()->clearConfig();
		parent::tearDown();
	}

	private function useConfig( string $configJson ): void {
		$this->overrideConfigValue( WikibaseFacetedSearchExtension::CONFIG_VARIABLE_NAME, $configJson );
		WikibaseFacetedSearchExtension::getInstance()->clearConfig();
	}

	private function useSearchEngine( SearchEngine $searchEngine ): void {
		$this->setService( 'SearchEngineFactory', new StubSearchEngineFactory( $searchEngine ) );
	}

	private function useSearchEngineWithoutHits(): SpySearchEngine {
		$searchEngine = new SpySearchEngine( Status::newGood( new EmptySearchResultSet( false ) ) );
		$this->useSearchEngine( $searchEngine );
		return $searchEngine;
	}

	private function doFacetSearch( array $params ): array {
		return $this->doApiRequest( [ 'action' => 'wbfacetsearch' ] + $params )[0];
	}

	private function getFacets( string $search ): array {
		return $this->doFacetSearch( [ 'search' => $search ] )['wbfacetsearch'];
	}

	public function testSearchWithoutItemTypeIsRejected(): void {
		$this->expectApiErrorCode( 'wbfs-item-type-required' );
		$this->getFacets( 'john' );
	}

	public function testSearchWithoutASearchExpressionIsRejected(): void {
		$this->expectApiErrorCode( 'missingparam' );
		$this->doFacetSearch( [] );
	}

	public function testAMalformedFacetFilterIsIgnored(): void {
		$this->useSearchEngineWithoutHits();

		$this->assertSame(
			[
				[ 'property' => 'P1', 'label' => 'P1', 'type' => 'list', 'values' => [] ],
				[ 'property' => 'P2', 'label' => 'P2', 'type' => 'range', 'values' => [] ]
			],
			$this->getFacets( self::SEARCH . ' haswbfacet:notAProperty=Q2' )
		);
	}

	public function testSearchIsRejectedWhenTheExtensionIsNotConfigured(): void {
		$this->useConfig( '{}' );

		$this->expectApiErrorCode( 'wbfs-not-configured' );
		$this->getFacets( self::SEARCH );
	}

	public function testItemTypeWithoutConfiguredFacetsYieldsAnEmptyList(): void {
		$this->useSearchEngineWithoutHits();

		$this->assertSame( [], $this->getFacets( 'haswbfacet:P42=Q404' ) );
	}

	public function testFacetsHaveNoValuesWhenTheSearchYieldsNoElasticsearchQuery(): void {
		$this->useSearchEngineWithoutHits();

		$this->assertSame(
			[
				[ 'property' => 'P1', 'label' => 'P1', 'type' => 'list', 'values' => [] ],
				[ 'property' => 'P2', 'label' => 'P2', 'type' => 'range', 'values' => [] ]
			],
			$this->getFacets( self::SEARCH )
		);
	}

	public function testFacetsAreReturnedForASearchWithAnElasticsearchQuery(): void {
		$this->useSearchEngine(
			new SpySearchEngine( Status::newGood( new StubCirrusSearchResultSet( new MatchAll() ) ) )
		);

		$this->assertSame(
			[ 'P1', 'P2' ],
			array_column( $this->getFacets( self::SEARCH ), 'property' )
		);
	}

	public function testSearchEngineFailureIsReportedToTheClient(): void {
		$this->useSearchEngine( new SpySearchEngine( Status::newFatal( 'cirrussearch-parse-error' ) ) );

		$this->expectApiErrorCode( 'cirrussearch-parse-error' );
		$this->getFacets( self::SEARCH );
	}

	public function testSearchEngineWarningsAreForwardedToTheClient(): void {
		$searchResult = Status::newGood( new EmptySearchResultSet( false ) );
		$searchResult->warning( 'cirrussearch-parse-error' );
		$this->useSearchEngine( new SpySearchEngine( $searchResult ) );

		$result = $this->doFacetSearch( [ 'search' => self::SEARCH, 'errorformat' => 'plaintext' ] );

		$this->assertSame( 'cirrussearch-parse-error', $result['warnings'][0]['code'] );
	}

	public function testTheSearchExpressionIsPassedToTheSearchEngine(): void {
		$searchEngine = $this->useSearchEngineWithoutHits();

		$this->getFacets( self::SEARCH );

		$this->assertSame( self::SEARCH, $searchEngine->searchedText );
	}

	public function testNoSearchHitsAreRequested(): void {
		$searchEngine = $this->useSearchEngineWithoutHits();

		$this->getFacets( self::SEARCH );

		$this->assertSame( 0, $searchEngine->requestedLimit );
	}

	public function testNoSearchSuggestionsAreRequested(): void {
		$searchEngine = $this->useSearchEngineWithoutHits();

		$this->getFacets( self::SEARCH );

		$this->assertFalse( $searchEngine->suggestionsRequested() );
	}

	public function testAllSearchableNamespacesAreSearchedByDefault(): void {
		$searchEngine = $this->useSearchEngineWithoutHits();

		$this->getFacets( self::SEARCH );

		$this->assertSame( $this->getSearchableNamespaceIds(), array_values( $searchEngine->namespaces ) );
	}

	/**
	 * @return int[]
	 */
	private function getSearchableNamespaceIds(): array {
		$namespaceIds = array_keys( $this->getServiceContainer()->getSearchEngineConfig()->searchableNamespaces() );
		sort( $namespaceIds );
		return $namespaceIds;
	}

	public function testOnlyTheRequestedNamespacesAreSearched(): void {
		$searchEngine = $this->useSearchEngineWithoutHits();

		$this->doFacetSearch( [ 'search' => self::SEARCH, 'namespaces' => '0|4' ] );

		$this->assertSame( [ NS_MAIN, NS_PROJECT ], array_values( $searchEngine->namespaces ) );
	}

	public function testFacetsAndValuesGetXmlElementNames(): void {
		$this->useSearchEngineWithoutHits();

		$result = $this->doApiRequest(
			[ 'action' => 'wbfacetsearch', 'search' => self::SEARCH ],
			null,
			true
		)[3]->getResult()->getResultData();

		$this->assertSame( 'facet', $result['wbfacetsearch']['_element'] );
		$this->assertSame( 'value', $result['wbfacetsearch'][0]['values']['_element'] );
	}

}
