<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query;

use CirrusSearch\Parser\AST\KeywordFeatureNode;
use CirrusSearch\Query\Builder\QueryBuildingContext;
use CirrusSearch\Query\FilterQueryFeature;
use CirrusSearch\Query\SimpleKeywordFeature;
use CirrusSearch\Search\SearchContext;
use Elastica\Query;
use Elastica\Query\AbstractQuery;

class HasWbFacetFeature extends SimpleKeywordFeature implements FilterQueryFeature {

	/**
	 * @return string[]
	 */
	protected function getKeywords(): array {
		return [ 'haswbfacet' ];
	}

	/**
	 * @return array
	 */
	protected function doApply( SearchContext $context, $key, $value, $quotedValue, $negated ): array {
		$query = null;

		// Example: date: date of birth
		if ( str_contains( $value, 'P593' ) ) {
			$query = new Query\Range( 'wbfs_P593', [
				'gte' => '1970-01-01'
			] );
		}
		// Example: keyword: sex or gender
		if ( str_contains( $value, 'P592' ) ) {
			$query = new Query\Term( [
				'wbfs_P592' => [ 'value' => 'Q57196' ] // male
//				'wbfs_P592' => [ 'value' => 'Q57505' ] // female
			] );
		}

		// TODO
		return [ $query, false ];
	}

	public function getFilterQuery( KeywordFeatureNode $node, QueryBuildingContext $context ): ?AbstractQuery {
		// TODO
		return null;
	}

}
