<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use Elastica\Response;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ElasticQueryRunner
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 * @group Database
 */
class ElasticQueryRunnerTest extends MediaWikiIntegrationTestCase {

	/**
	 * The test database prefix ends up in the wiki id, and thus in the index name derived from it,
	 * for which no index exists. Point the tests at the index this wiki actually has.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->overrideConfigValue( 'CirrusSearchIndexBaseName', $this->getDb()->getDBname() );
	}

	public function testCanQueryElastic(): void {
		$query = [
			'query' => [
				'match_all' => new \stdClass()
			]
		];

		$resultSet = $this->runQuery( $query )->getData();

		$this->assertArrayHasKey( 'hits', $resultSet );
		$this->assertGreaterThan( 0, $resultSet['hits']['total']['value'] );
	}

	private function runQuery( array $query ): Response {
		return WikibaseFacetedSearchExtension::getInstance()->getElasticQueryRunner()->runQuery( $query );
	}

	public function testFindsNothingWhenTheIndexOfThisWikiIsMissing(): void {
		$this->overrideConfigValue( 'CirrusSearchIndexBaseName', 'wbfs_test_missing_index' );

		$resultSet = $this->runQuery( [ 'query' => [ 'match_all' => new \stdClass() ] ] )->getData();

		$this->assertSame( 0, $resultSet['hits']['total']['value'] );
	}

	public function testCanQueryMediaWikiPage(): void {
		$query = [
			'query' => [
				'bool' => [
					'must' => [
						[
							'match' => [
								'title' => 'Main Page'
							]
						]
					]
				]
			]
		];

		$resultSet = $this->runQuery( $query )->getData();

		if ( !array_key_exists( 0, $resultSet['hits']['hits'] ) ) {
			$this->markTestSkipped( 'Main Page not indexed yet' );
		}

		$this->assertSame(
			"MediaWiki has been installed. Consult the User's Guide for information on using the wiki software.",
			$resultSet['hits']['hits'][0]['_source']['opening_text']
		);
	}

}
