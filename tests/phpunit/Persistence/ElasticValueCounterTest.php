<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use CirrusSearch\Connection;
use CirrusSearch\SearchConfig;
use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query\MatchAll;
use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCount;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ElasticValueCounter
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ElasticQueryRunner
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 */
class ElasticValueCounterTest extends MediaWikiIntegrationTestCase {

	private const PROPERTY_ID = 'P22';
	private const FIELD = 'wbfs_' . self::PROPERTY_ID;
	private const OWN_INDEX_NAME = 'wbfs_test_this_wiki';
	private const FOREIGN_INDEX_NAME = 'wbfs_test_other_wiki';

	/**
	 * @var Index[]
	 */
	private array $createdIndexes = [];

	protected function tearDown(): void {
		foreach ( $this->createdIndexes as $index ) {
			$index->delete();
		}

		parent::tearDown();
	}

	public function testCountsOnlyTheDocumentsOfThisWiki(): void {
		$this->createIndexContainingValue( self::OWN_INDEX_NAME, 'Q2' );
		$this->overrideConfigValue( 'CirrusSearchIndexBaseName', self::OWN_INDEX_NAME );

		$this->createIndexContainingValue( self::FOREIGN_INDEX_NAME, 'Q1' );

		$this->assertSame( [ 'Q2' ], $this->countedValues() );
	}

	private function createIndexContainingValue( string $indexName, string $value ): void {
		$index = $this->getElasticaClient()->getIndex( $indexName );
		$this->createdIndexes[] = $index;

		$index->create(
			[ 'mappings' => [ 'properties' => [ self::FIELD => [ 'type' => 'keyword' ] ] ] ],
			[ 'recreate' => true ]
		);

		$index->addDocument( new Document( '1', [ self::FIELD => [ $value ] ] ) );
		$index->refresh();
	}

	private function getElasticaClient(): Client {
		/** @var SearchConfig $searchConfig */
		$searchConfig = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'CirrusSearch' );
		return Connection::getPool( $searchConfig )->getClient();
	}

	/**
	 * @return array<string|float|int>
	 */
	private function countedValues(): array {
		$valueCounts = WikibaseFacetedSearchExtension::getInstance()
			->getValueCounter( new MatchAll() )
			->countValues( new PropertyConstraints( new NumericPropertyId( self::PROPERTY_ID ) ) );

		return array_map(
			fn( ValueCount $valueCount ) => $valueCount->value,
			$valueCounts->asArray()
		);
	}

}
