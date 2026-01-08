<?php

declare( strict_types=1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\EntryPoints;

use ApiBase;
use CirrusSearch\Search\CirrusSearchResultSet;
use Elastica\Query\AbstractQuery;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

class ApiWikibaseFacetedSearch extends ApiBase {

	public function execute(): void {
		try {
			$params = $this->extractRequestParams();
			$term = $params['search'];
			$namespaces = $params['namespaces'];

			$facets = $this->getFacets( $term, $namespaces );

			$this->getResult()->addValue( null, $this->getModuleName(), $facets );
		} catch ( \Throwable $e ) {
			$this->getResult()->addValue( null, 'error', [
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
				'file' => $e->getFile(),
				'line' => $e->getLine()
			] );
		}
	}

	private function getFacets( string $term, ?array $namespaces ): array {
		$results = $this->runSearch( $term, $namespaces );

		if ( $results instanceof Status ) {
			if ( !$results->isOK() ) {
				return [];
			}
			$results = $results->getValue();
		}

		if ( !$results instanceof CirrusSearchResultSet ) {
			return [];
		}

		$elasticaResultSet = $results->getElasticaResultSet();
		if ( $elasticaResultSet === null ) {
			return [];
		}
		$query = $elasticaResultSet->getQuery()->getQuery();

		if ( is_array( $query ) ) {
			$queryArr = $query;
			$query = new class ( $queryArr ) extends AbstractQuery {
				public function __construct( private array $arr ) {
				}

				public function toArray(): array {
					return $this->arr;
				}
			};
		}

		$extension = WikibaseFacetedSearchExtension::getInstance();
		$queryStringParser = $extension->getQueryStringParser();
		$parsedQuery = $queryStringParser->parse( $term );

		$itemType = $parsedQuery->getItemTypes()[0] ?? null;
		if ( !$itemType ) {
			return [];
		}

		$config = $extension->getConfig();
		$facetConfigs = $config->getFacetConfigForItemType( $itemType );

		$data = [];
		$valueCounter = $extension->getValueCounter( $query );
		$labelLookup = $extension->getLabelLookup( $this->getLanguage() );
		$formatter = $extension->getFacetValueFormatter( $this->getLanguage() );

		$data = [];
		foreach ( $facetConfigs as $facetConfig ) {
			$data[] = $this->buildFacetData(
				$facetConfig,
				$parsedQuery,
				$valueCounter,
				$labelLookup,
				$formatter
			);
		}

		return $data;
	}

	private function runSearch( string $term, ?array $namespaces ) {
		$searchEngine = MediaWikiServices::getInstance()->newSearchEngine();
		$searchEngine->setLimitOffset( 1 );

		if ( $namespaces ) {
			$searchEngine->setNamespaces( $namespaces );
		}

		return $searchEngine->searchText( $term );
	}

	private function buildFacetData(
		$facetConfig,
		$parsedQuery,
		$valueCounter,
		$labelLookup,
		$formatter
	): array {
		$constraints = $parsedQuery->getConstraintsForProperty( $facetConfig->propertyId )
			?? new PropertyConstraints( $facetConfig->propertyId );

		$counts = $valueCounter->countValues( $constraints );

		$propertyId = $facetConfig->propertyId;
		$propertySerialization = $propertyId->getSerialization();

		$facetData = [
			'property' => $propertySerialization,
			'label' => $labelLookup->getLabel( $propertyId )?->getText() ?? $propertySerialization,
			'values' => [],
		];

		foreach ( $counts->asArray() as $valCount ) {
			$facetData['values'][] = [
				'value' => $valCount->value,
				'count' => $valCount->count,
				'label' => $formatter->getLabel( (string)$valCount->value, $propertyId ),
			];
		}

		return $facetData;
	}
}
