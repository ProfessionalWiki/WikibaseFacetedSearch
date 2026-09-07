<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\EntryPoints;

use ApiMain;
use ApiUsageException;
use MediaWiki\Request\FauxRequest;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseFacetedSearch\EntryPoints\ApiWikibaseFacetedSearch;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use RuntimeException;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\EntryPoints\ApiWikibaseFacetedSearch
 */
class ApiWikibaseFacetedSearchTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->overrideConfigValues( [
			'WikibaseFacetedSearchEnableInWikiConfig' => false,
			'WikibaseFacetedSearch' => json_encode( [
				'sitelinkSiteId' => null,
				'itemTypeProperty' => 'P1',
				'configPerItemType' => [ 'Q1' => [ 'facets' => [ 'P2' => [ 'type' => 'list' ] ] ] ],
			] ),
		] );
		WikibaseFacetedSearchExtension::getInstance()->clearConfig();
	}

	protected function tearDown(): void {
		WikibaseFacetedSearchExtension::getInstance()->clearConfig();
		parent::tearDown();
	}

	/** @return ApiWikibaseFacetedSearch&\PHPUnit\Framework\MockObject\MockObject */
	private function newApi( string $search ): ApiWikibaseFacetedSearch {
		return $this->getMockBuilder( ApiWikibaseFacetedSearch::class )
			->setConstructorArgs( [
				new ApiMain( new FauxRequest( [ 'search' => $search, 'namespaces' => '0' ] ) ),
				'wbfacetsearch',
			] )
			->onlyMethods( [ 'runSearch' ] )
			->getMock();
	}

	public function testMissingTypeFailsBeforeSearch(): void {
		$api = $this->newApi( 'poetry' );
		$api->expects( $this->never() )->method( 'runSearch' );
		$this->expectException( ApiUsageException::class );
		$api->execute();
	}

	public function testUnconfiguredTypeFailsBeforeSearch(): void {
		$api = $this->newApi( 'haswbfacet:P1=Q999' );
		$api->expects( $this->never() )->method( 'runSearch' );
		$this->expectException( ApiUsageException::class );
		$api->execute();
	}

	public function testUnavailableSearchIsNotAnEmptyFacetResponse(): void {
		$api = $this->newApi( 'haswbfacet:P1=Q1' );
		$api->method( 'runSearch' )->willReturn( null );
		$this->expectException( ApiUsageException::class );
		$api->execute();
	}

	public function testInternalExceptionDoesNotExposeDetails(): void {
		$api = $this->newApi( 'haswbfacet:P1=Q1' );
		$api->method( 'runSearch' )->willThrowException( new RuntimeException( '/private/server/secret' ) );
		try {
			$api->execute();
			$this->fail( 'Expected an API error' );
		} catch ( ApiUsageException $error ) {
			$this->assertStringNotContainsString( '/private/server/secret', $error->getMessage() );
		}
	}
}
