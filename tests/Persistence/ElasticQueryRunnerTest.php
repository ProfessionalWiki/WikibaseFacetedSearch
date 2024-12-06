<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use CirrusSearch\BuildDocument\BuildDocument;
use CirrusSearch\Connection;
use CirrusSearch\SearchConfig;
use CirrusSearch\Updater;
use Elastica\Client;
use Elastica\Response;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use WikiPage;

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
		$title = Title::newFromText( 'ElasticQueryRunnerTest' );

		$status = $this->editPage(
			$title,
			new \WikitextContent( 'To search or not to search, that is the query.' )
		);

		$this->assertTrue( $status->isGood() );

		$page = new WikiPage( $title );

		$this->assertSame( 'To search or not to search, that is the query.', $page->getContent()->getTextForSummary() );

		$updater = new Updater( $this->getConnection() );
		$updater->updatePages( [ $page ], BuildDocument::INDEX_EVERYTHING );

		$this->getClient()->refreshAll();
		sleep( 1 );

		$query = [
			'query' => [
				'bool' => [
					'must' => [
						[
							'match' => [
								'title' => 'ElasticQueryRunnerTest'
							]
						]
					]
				]
			]
		];

		$resultSet = $this->runQuery( $query )->getData();

		// FIXME: no restuls are found
		$this->assertSame(
			"MediaWiki has been installed. Consult the User's Guide for information on using the wiki software.",
			$resultSet['hits']['hits'][0]['_source']['opening_text']
		);
	}

}
