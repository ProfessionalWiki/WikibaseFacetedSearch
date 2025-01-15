<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use CirrusSearch\Connection;
use CirrusSearch\SearchConfig;
use Elastica\Client;
use Elastica\Response;
use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;

/**
 * @group Database
 */
class ElasticQueryRunnerTest extends MediaWikiIntegrationTestCase {

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
		return $this->getClient()->request( '_search', 'GET', $query );
	}

	private function getClient(): Client {
		return $this->getConnection()->getClient();
	}

	private function getConnection(): Connection {
		return new Connection( $this->getSearchConfig() );
	}

	private function getSearchConfig(): SearchConfig {
		/**
		 * @var SearchConfig $config
		 */
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'CirrusSearch' );
		return $config;
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
