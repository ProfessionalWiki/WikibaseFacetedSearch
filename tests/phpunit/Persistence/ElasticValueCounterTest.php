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
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCount;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ElasticValueCounter
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ElasticQueryRunner
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 */
class ElasticValueCounterTest extends TestCase {

	private const PROPERTY_ID = 'P22';
	private const FIELD = 'wbfs_' . self::PROPERTY_ID;
	private const FOREIGN_INDEX_NAME = 'wbfs_test_other_wiki';

	private ?Index $foreignIndex = null;

	protected function tearDown(): void {
		$this->foreignIndex?->delete();
		$this->foreignIndex = null;

		parent::tearDown();
	}

	public function testCanExecuteValueCountQuery(): void {
		$counter = WikibaseFacetedSearchExtension::getInstance()->getValueCounter( new MatchAll() );
		$counter->countValues( new PropertyConstraints( new NumericPropertyId( self::PROPERTY_ID ) ) );
		$this->assertTrue( true );
	}

	public function testDoesNotCountDocumentsOfOtherWikisOnTheSameCluster(): void {
		$this->createForeignIndexContainingValue( 'Q1' );

		$this->assertNotContains( 'Q1', $this->countedValues() );
	}

	private function createForeignIndexContainingValue( string $value ): void {
		$index = $this->getElasticaClient()->getIndex( self::FOREIGN_INDEX_NAME );
		$index->create(
			[ 'mappings' => [ 'properties' => [ self::FIELD => [ 'type' => 'keyword' ] ] ] ],
			[ 'recreate' => true ]
		);
		$this->foreignIndex = $index;

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
