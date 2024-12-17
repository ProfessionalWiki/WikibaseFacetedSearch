<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query;

use CirrusSearch\Parser\AST\KeywordFeatureNode;
use CirrusSearch\Query\Builder\QueryBuildingContext;
use CirrusSearch\Query\FilterQueryFeature;
use CirrusSearch\Query\SimpleKeywordFeature;
use CirrusSearch\Search\SearchContext;
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
		// TODO
		return [ null, false ];
	}

	public function getFilterQuery( KeywordFeatureNode $node, QueryBuildingContext $context ): ?AbstractQuery {
		// TODO
		return null;
	}

}
