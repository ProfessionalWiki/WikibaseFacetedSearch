<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search;

use CirrusSearch\CirrusSearch;
use CirrusSearch\Search\DatetimeIndexField;
use CirrusSearch\Search\NumberIndexField;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use SearchIndexField;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Services\Lookup\InMemoryDataTypeLookup;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\SearchIndexFieldsBuilder
 */
class SearchIndexFieldsBuilderTest extends TestCase {

	private CirrusSearch $cirrusSearch;

	public function setUp(): void {
		parent::setUp();
		$this->cirrusSearch = new CirrusSearch();
	}

	public function testEmptyConfigReturnsNoFields(): void {
		$builder = $this->newBuilder( new Config() );

		$this->assertSame( [], $builder->createFields() );
	}

	private function newBuilder( Config $config ): SearchIndexFieldsBuilder {
		return new SearchIndexFieldsBuilder(
			$this->cirrusSearch,
			$config,
			$this->newDataTypeLookup()
		);
	}

	public function testConfigWithoutItemTypeReturnsNoFields(): void {
		$builder = $this->newBuilder( new Config(
			facets: new FacetConfigList(
				$this->newFacetConfig( 'Q1', 'P100' ),
				$this->newFacetConfig( 'Q1', 'P200' ),
			)
		) );

		$this->assertSame( [], $builder->createFields() );
	}

	private function newDataTypeLookup(): InMemoryDataTypeLookup {
		$dataTypeLookup = new InMemoryDataTypeLookup();

		$types = [
			'P100' => 'quantity',
			'P200' => 'string',
			'P300' => 'time',
			'P400' => 'wikibase-item'
		];

		foreach ( $types as $pId => $type ) {
			$dataTypeLookup->setDataTypeForProperty(
				new NumericPropertyId( $pId ),
				$type
			);
		}

		return $dataTypeLookup;
	}

	public function testReturnsFieldForItemType(): void {
		$builder = $this->newBuilder(
			new Config(
				instanceOfId: new NumericPropertyId( 'P1' )
			)
		);

		$this->assertEquals(
			[
				'wbfs_P1' => new AggregatableKeywordIndexField( 'wbfs_P1', SearchIndexField::INDEX_TYPE_KEYWORD, $this->cirrusSearch->getConfig() )
			],
			$builder->createFields()
		);
	}

	public function testReturnsFieldsForConfig(): void {
		$builder = $this->newBuilder(
			new Config(
				instanceOfId: new NumericPropertyId( 'P1' ),
				facets: new FacetConfigList(
					$this->newFacetConfig( 'Q1', 'P100' ),
					$this->newFacetConfig( 'Q1', 'P200' ),
					$this->newFacetConfig( 'Q2', 'P100' ),
					$this->newFacetConfig( 'Q2', 'P300' ),
					$this->newFacetConfig( 'Q3', 'P400' )
				)
			)
		);

		$this->assertEquals(
			[
				'wbfs_P1' => new AggregatableKeywordIndexField( 'wbfs_P1', SearchIndexField::INDEX_TYPE_KEYWORD, $this->cirrusSearch->getConfig() ),
				'wbfs_P100' => new NumberIndexField( 'wbfs_P100', SearchIndexField::INDEX_TYPE_NUMBER, $this->cirrusSearch->getConfig() ),
				'wbfs_P200' => new AggregatableKeywordIndexField( 'wbfs_P200', SearchIndexField::INDEX_TYPE_KEYWORD, $this->cirrusSearch->getConfig() ),
				'wbfs_P300' => new DatetimeIndexField( 'wbfs_P300', SearchIndexField::INDEX_TYPE_DATETIME, $this->cirrusSearch->getConfig() ),
				'wbfs_P400' => new AggregatableKeywordIndexField( 'wbfs_P400', SearchIndexField::INDEX_TYPE_KEYWORD, $this->cirrusSearch->getConfig() )
			],
			$builder->createFields()
		);
	}

	private function newFacetConfig( string $instanceTypeId, string $propertyId, ?string $facetType = null ): FacetConfig {
		return new FacetConfig(
			new ItemId( $instanceTypeId ),
			new NumericPropertyId( $propertyId ),
			$facetType ?? FacetType::LIST
		);
	}

	private function newProperty( string $id, string $dataType ): Property {
		return new Property( new NumericPropertyId( $id ), null, $dataType );
	}

	public function testDoesNotReturnFieldsForMissingProperties(): void {
		$builder = $this->newBuilder(
			new Config(
				instanceOfId: new NumericPropertyId( 'P1' ),
				facets: new FacetConfigList(
					$this->newFacetConfig( 'Q1', 'P100' ),
					$this->newFacetConfig( 'Q2', 'P404' ),
					$this->newFacetConfig( 'Q3', 'P200' )
				)
			)
		);

		$this->assertEquals(
			[
				'wbfs_P1' => new AggregatableKeywordIndexField( 'wbfs_P1', SearchIndexField::INDEX_TYPE_KEYWORD, $this->cirrusSearch->getConfig() ),
				'wbfs_P100' => new NumberIndexField( 'wbfs_P100', SearchIndexField::INDEX_TYPE_NUMBER, $this->cirrusSearch->getConfig() ),
				'wbfs_P200' => new AggregatableKeywordIndexField( 'wbfs_P200', SearchIndexField::INDEX_TYPE_KEYWORD, $this->cirrusSearch->getConfig() )
			],
			$builder->createFields()
		);
	}

}
