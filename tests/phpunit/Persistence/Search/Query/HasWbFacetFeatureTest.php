<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence\Search\Query;

use CirrusSearch\Query\KeywordFeatureAssertions;
use CirrusSearch\Search\SearchContext;
use CirrusSearch\SearchConfig;
use Elastica\Query\Terms;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\DelegatingFacetQueryBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\HasWbFacetFeature;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\ItemTypeQueryBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\WikibaseFacetedSearchIntegrationTest;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\HasWbFacetFeature
 */
class HasWbFacetFeatureTest extends WikibaseFacetedSearchIntegrationTest {

	protected function setUp(): void {
		parent::setUp();

		$this->markTestSkippedIfExtensionNotLoaded( 'CirrusSearch' );
	}

	/**
	 * @dataProvider noDataProvider
	 */
	public function testNotConsumed( $term ) {
		$feature = $this->newHasWbFacetFeature();
		$this->getKWAssertions()->assertNotConsumed( $feature, $term );
	}

	public static function noDataProvider() {
		return [
			'empty data' => [
				'haswbfacet:',
			],
			'no data' => [
				'',
			],
		];
	}

	public function testFiltersOnEveryItemTypeOfAnOrValue(): void {
		$term = 'haswbfacet:P42=Q68|Q67|Q69';
		$context = $this->newSearchContext( $term );

		$this->newHasWbFacetFeature()->apply( $context, $term );

		$this->assertEquals( [ new Terms( 'wbfs_P42', [ 'Q68', 'Q67', 'Q69' ] ) ], $context->getFilters() );
	}

	// KeywordFeatureAssertions::assertFilter is not used: it requires a FilterQueryFeature and never stubs getOriginalSearchTerm().
	private function newSearchContext( string $term ): SearchContext {
		$context = new SearchContext( new SearchConfig() );
		$context->setOriginalSearchTerm( $term );
		return $context;
	}

	private function newHasWbFacetFeature(): HasWbFacetFeature {
		return new HasWbFacetFeature(
			new Config(),
			new QueryStringParser( new NumericPropertyId( 'P42' ) ),
			new ItemTypeQueryBuilder( new NumericPropertyId( 'P42' ) ),
			new DelegatingFacetQueryBuilder()
		);
	}

	/**
	 * @return KeywordFeatureAssertions
	 */
	private function getKWAssertions() {
		return new KeywordFeatureAssertions( $this );
	}

}
