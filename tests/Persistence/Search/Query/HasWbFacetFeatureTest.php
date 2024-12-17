<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence\Search\Query;

use CirrusSearch\Query\KeywordFeatureAssertions;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\HasWbFacetFeature;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\WikibaseFacetedSearchIntegrationTest;

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
		$feature = new HasWbFacetFeature();
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

	/**
	 * @return KeywordFeatureAssertions
	 */
	private function getKWAssertions() {
		return new KeywordFeatureAssertions( $this );
	}

}
