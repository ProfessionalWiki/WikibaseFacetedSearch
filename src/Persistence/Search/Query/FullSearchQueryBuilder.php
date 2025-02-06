<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query;

use CirrusSearch\CirrusDebugOptions;
use CirrusSearch\CirrusSearch;
use CirrusSearch\CirrusSearchHookRunner;
use CirrusSearch\Fallbacks\FallbackRunner;
use CirrusSearch\InterwikiResolver;
use CirrusSearch\Parser\BasicQueryClassifier;
use CirrusSearch\Parser\FullTextKeywordRegistry;
use CirrusSearch\Parser\NamespacePrefixParser;
use CirrusSearch\Profile\ContextualProfileOverride;
use CirrusSearch\Profile\SearchProfileService;
use CirrusSearch\Query\PrefixFeature;
use CirrusSearch\Search\CirrusSearchResultSet;
use CirrusSearch\Search\FullTextResultsType;
use CirrusSearch\Search\SearchContext;
use CirrusSearch\Search\SearchQuery;
use CirrusSearch\Search\SearchQueryBuilder;
use CirrusSearch\Search\SearchRequestBuilder;
use CirrusSearch\Search\TitleHelper;
use CirrusSearch\SearchConfig;
use CirrusSearch\Searcher;
use Elastica\Query\AbstractQuery;
use Elastica\Query\MatchAll;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\WikiMap\WikiMap;
use SearchEngine;

class FullSearchQueryBuilder {

	private ?CirrusSearchHookRunner $cirrusSearchHookRunner = null;

	public function __construct(
		private RequestContext $requestContext,
		private CirrusSearch $cirrusSearch
	) {
	}

	public function buildQuery( string $term ): AbstractQuery {
		$query = $this->newSearchQueryBuilder( $term )->build();

		// CirrusSearch::searchTextReal()
		$searcher = $this->makeSearcher( $query->getSearchConfig() );

		// TODO: don't run the search
		// TODO: duplicate Searcher::search()
		// TODO: duplicate Searcher::searchTextInternal() ?
		$status = $searcher->search( $query );
		$results = $status->getValue();

		if ( !( $results instanceof CirrusSearchResultSet ) ) {
			return new MatchAll();
		}

		return $results->getElasticaResultSet()->getQuery()->getQuery();
	}

	/**
	 * @see CirrusSearch::doSearchText()
	 */
	private function newSearchQueryBuilder( string $term ): SearchQueryBuilder {
		$builder = SearchQueryBuilder::newFTSearchQueryBuilder(
			$this->cirrusSearch->getConfig(),
			$term,
			$this->getNamespacePrefixParser(),
			$this->getCirrusSearchHookRunner()
		);

		$builder->setDebugOptions( $this->getDebugOptions() )
			->setInitialNamespaces( $this->cirrusSearch->namespaces )
			->setLimit( 10 ) // TODO
			->setOffset( 0 ) // TODO
			->setSort( $this->cirrusSearch->getSort() )
			->setRandomSeed( $this->cirrusSearch->getFeatureData( 'random_seed' ) )
			->setExtraIndicesSearch( true )
			->setCrossProjectSearch( $this->isFeatureEnabled( $this->cirrusSearch, 'interwiki' ) )
			->setWithDYMSuggestion( false ) // TODO
			->setAllowRewrite( $this->isFeatureEnabled( $this->cirrusSearch,  'rewrite' ) )
			->addProfileContextParameter(
				ContextualProfileOverride::LANGUAGE,
				$this->requestContext->getLanguage()->getCode()
			)
			->setExtraFieldsToExtract( $this->cirrusSearch->getFeatureData( CirrusSearch::EXTRA_FIELDS_TO_EXTRACT ) ?? [] )
			->setProvideAllSnippets( !empty( $this->cirrusSearch->getFeatureData( 'snippets' ) ) );

		if ( $this->cirrusSearch->prefix !== '' ) {
			$builder->addContextualFilter( 'prefix', PrefixFeature::asContextualFilter( $this->cirrusSearch->prefix ) );
		}

		$profile = $this->cirrusSearch->extractProfileFromFeatureData( SearchEngine::FT_QUERY_INDEP_PROFILE_TYPE );
		if ( $profile !== null ) {
			$builder->addForcedProfile( SearchProfileService::RESCORE, $profile );
		}

		return $builder;
	}

	private function getNamespacePrefixParser(): NamespacePrefixParser {
		return new class() implements NamespacePrefixParser {
			public function parse( $query ) {
				return CirrusSearch::parseNamespacePrefixes( $query, true, true );
			}
		};
	}

	private function getCirrusSearchHookRunner(): CirrusSearchHookRunner {
		if ( $this->cirrusSearchHookRunner == null ) {
			$this->cirrusSearchHookRunner = new CirrusSearchHookRunner(
				MediaWikiServices::getInstance()->getHookContainer()
			);
		}
		return $this->cirrusSearchHookRunner;
	}

	private function getDebugOptions(): CirrusDebugOptions {
		return CirrusDebugOptions::fromRequest( $this->requestContext->getRequest() );
	}


	private function isFeatureEnabled( CirrusSearch $cirrusSearch, string $feature ): bool {
		return $cirrusSearch->getFeatureData( $feature ) !== null && $cirrusSearch->getFeatureData( $feature ) !== false;
	}

	private function makeSearcher( SearchConfig $config ) {
		return new Searcher(
			$this->cirrusSearch->getConnection(),
			0,
			10,
			$config,
			$this->cirrusSearch->namespaces,
			null,
			false,
			$this->getDebugOptions(),
			$this->getNamespacePrefixParser(),
			$this->getInterwikiResolver(),
			$this->getTitleHelper(),
			$this->getCirrusSearchHookRunner()
		);
	}

	private function buildSearch( SearchContext $searchContext ) {
		$builder = new SearchRequestBuilder(
			$searchContext,
			$this->cirrusSearch->getConnection(),
			$this->cirrusSearch->getConfig()->get( SearchConfig::INDEX_BASE_NAME )
		);
		return $builder->setLimit( 10 )
			->setOffset( 0 )
			->setIndex( $this->cirrusSearch->getConnection()->getIndex( $this->cirrusSearch->getConfig()->get( SearchConfig::INDEX_BASE_NAME ) ) )
			->setSort( $searchContext->getSearchQuery()->getSort() )//$this->sort )
			->setTimeout( '0s' )//$this->getTimeout( $searchContext->getSearchType() ) )
			->build();
	}

	private function newSearchContext( SearchQuery $query ): SearchContext {
		$searchContext = SearchContext::fromSearchQuery(
			$query,
			FallbackRunner::create( $query, $this->getInterwikiResolver() ),
			$this->cirrusSearchHookRunner
		);
//		$this->setOffsetLimit( $query->getOffset(), $query->getLimit() );
//		$this->config = $query->getSearchConfig();
//		$this->sort = $query->getSort();

		$searchContext->setResultsType(
			new FullTextResultsType(
				$searchContext->getFetchPhaseBuilder(),
				$query->getParsedQuery()->isQueryOfClass( BasicQueryClassifier::COMPLEX_QUERY ),
				$this->getTitleHelper(),
				$query->getExtraFieldsToExtract(),
				$searchContext->getConfig()->getElement( 'CirrusSearchDeduplicateInMemory' ) === true
			)
		);

		return $searchContext;
	}

	private function getInterwikiResolver(): InterwikiResolver {
		return MediaWikiServices::getInstance()->getService( InterwikiResolver::SERVICE );
	}

	private function getTitleHelper(): TitleHelper {
		return new TitleHelper( WikiMap::getCurrentWikiId(), $this->getInterwikiResolver() );
	}

}
