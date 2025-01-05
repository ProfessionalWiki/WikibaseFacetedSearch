<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence\Search\Query;

use Elastica\Query;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\ItemTypeQueryBuilder;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\ItemTypeQueryBuilder
 */
class ItemTypeQueryBuilderTest extends TestCase {

	private const ITEM_TYPE_PROPERTY = 'P42';

	public function testBuildsTermsQueryWithSingleItemType(): void {
		$itemTypeQueryBuilder = $this->newItemTypeQueryBuilder();

		$query = $itemTypeQueryBuilder->buildQuery( [
			new ItemId( 'Q100' )
		] );

		$this->assertEquals(
			new Query\Terms(
				'wbfs_P42',
				[ 'Q100' ]
			),
			$query
		);
	}

	private function newItemTypeQueryBuilder(): ItemTypeQueryBuilder {
		return new ItemTypeQueryBuilder( new NumericPropertyId( self::ITEM_TYPE_PROPERTY ) );
	}

	public function testBuildsTermsQueryWithMultipleItemTypes(): void {
		$itemTypeQueryBuilder = $this->newItemTypeQueryBuilder();

		$query = $itemTypeQueryBuilder->buildQuery( [
			new ItemId( 'Q100' ),
			new ItemId( 'Q200' )
		] );

		$this->assertEquals(
			new Query\Terms(
				'wbfs_P42',
				[ 'Q100', 'Q200' ]
			),
			$query
		);
	}

}
