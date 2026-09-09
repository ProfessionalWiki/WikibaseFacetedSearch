<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\EntryPoints;

use CirrusSearch\Search\CirrusSearchResultSet;
use Elastica\Query\AbstractQuery;
use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiResult;
use MediaWiki\Status\Status;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use RuntimeException;
use SearchEngine;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * Returns the facets configured for the item type of a search expression, with the value
 * counts for that search.
 */
class ApiWikibaseFacetedSearch extends ApiBase {

	public function execute(): void {
		$extension = WikibaseFacetedSearchExtension::getInstance();

		if ( !$extension->getConfig()->isComplete() ) {
			$this->dieWithError( 'apierror-wbfs-not-configured', 'wbfs-not-configured' );
		}

		$params = $this->extractRequestParams();
		/** @var string $search */
		$search = $params['search'];
		/** @var int[] $namespaces */
		$namespaces = $params['namespaces'];

		$query = $extension->getQueryStringParser()->parse( $search );

		if ( $query->getItemTypes() === [] ) {
			$this->dieWithError( 'apierror-wbfs-item-type-required', 'wbfs-item-type-required' );
		}

		$resultSet = $this->runSearch( $extension->newSearchEngine(), $search, $namespaces );

		$this->addFacetsToResult(
			$extension->newFacetsResponseBuilder(
				$this->getLanguage(),
				$this->extractElasticsearchQuery( $resultSet )
			)->buildFacets( $query )
		);
	}

	/**
	 * @param int[] $namespaces
	 */
	private function runSearch( SearchEngine $searchEngine, string $search, array $namespaces ): CirrusSearchResultSet {
		$searchEngine->setNamespaces( $namespaces );
		$searchEngine->setLimitOffset( 0 );
		$searchEngine->setShowSuggestion( false );

		$result = $searchEngine->searchText( $search );

		if ( $result instanceof Status ) {
			if ( !$result->isOK() ) {
				$this->dieStatus( $result );
			}

			$this->addMessagesFromStatus( $result );
			$result = $result->getValue();
		}

		if ( $result instanceof CirrusSearchResultSet ) {
			return $result;
		}

		throw new RuntimeException( 'WikibaseFacetedSearch requires CirrusSearch to be the active search engine' );
	}

	/**
	 * Null when the search yielded no Elasticsearch query to aggregate over, such as when
	 * the search engine determined up front that nothing can match.
	 */
	private function extractElasticsearchQuery( CirrusSearchResultSet $resultSet ): ?AbstractQuery {
		$elasticaResultSet = $resultSet->getElasticaResultSet();

		if ( $elasticaResultSet === null ) {
			return null;
		}

		$query = $elasticaResultSet->getQuery()->getQuery();

		if ( $query instanceof AbstractQuery ) {
			return $query;
		}

		throw new RuntimeException( 'The search engine provided a raw Elasticsearch query, which cannot be filtered' );
	}

	private function addFacetsToResult( array $facets ): void {
		foreach ( array_keys( $facets ) as $i ) {
			ApiResult::setIndexedTagName( $facets[$i]['values'], 'value' );
		}

		ApiResult::setIndexedTagName( $facets, 'facet' );

		$this->getResult()->addValue( null, $this->getModuleName(), $facets );
	}

	public function getAllowedParams(): array {
		return [
			'search' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'namespaces' => [
				ParamValidator::PARAM_TYPE => 'namespace',
				ParamValidator::PARAM_ISMULTI => true,
				ParamValidator::PARAM_DEFAULT => ParamValidator::ALL_DEFAULT_STRING
			]
		];
	}

	protected function getExamplesMessages(): array {
		return [
			'action=wbfacetsearch&search=haswbfacet:P1=Q1' => 'apihelp-wbfacetsearch-example-1'
		];
	}

}
