<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\EntryPoints;

use ApiBase;
use ApiUsageException;
use CirrusSearch\Search\CirrusSearchResultSet;
use Elastica\Query\AbstractQuery;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use RuntimeException;

class ApiWikibaseFacetedSearch extends ApiBase {

	public function getAllowedParams(): array {
		return [
			'search' => [
				self::PARAM_TYPE => 'string',
				self::PARAM_REQUIRED => true,
			],
			'namespaces' => [
				self::PARAM_TYPE => 'namespace',
				self::PARAM_ISMULTI => true,
				self::PARAM_REQUIRED => false,
			],
		];
	}

	public function execute(): void {
		try {
			$params = $this->extractRequestParams();
			$term = $params['search'];
			$namespaces = $params['namespaces'];

			$facets = $this->getFacets( $term, $namespaces );

			$this->getResult()->addValue( null, $this->getModuleName(), $facets );
		} catch ( ApiUsageException $e ) {
			throw $e;
		} catch ( \Throwable $e ) {
			wfLogWarning( 'WikibaseFacetedSearch API: ' . $e->getMessage() );
			$this->dieWithError( 'apierror-wbfs-search-failed', 'wbfs-search-failed' );
		}
	}

	private function getFacets( string $term, ?array $namespaces ): array {
		$extension = WikibaseFacetedSearchExtension::getInstance();
		$queryStringParser = $extension->getQueryStringParser();
		$parsedQuery = $queryStringParser->parse( $term );

		$itemType = $parsedQuery->getItemTypes()[0] ?? null;
		if ( !$itemType ) {
			$this->dieWithError( 'apierror-wbfs-item-type-required', 'wbfs-item-type-required' );
		}

		$config = $extension->getConfig();
		$facetConfigs = $config->getFacetConfigForItemType( $itemType );
		if ( $facetConfigs === [] ) {
			$this->dieWithError( 'apierror-wbfs-no-facets', 'wbfs-no-facets' );
		}

		$results = $this->runSearch( $term, $namespaces );

		if ( $results instanceof Status ) {
			if ( !$results->isOK() ) {
				throw new RuntimeException( 'Search engine returned a failed status' );
			}
			$results = $results->getValue();
		}

		if ( !$results instanceof CirrusSearchResultSet ) {
			throw new RuntimeException( 'Expected a CirrusSearch result set' );
		}

		$elasticaResultSet = $results->getElasticaResultSet();
		if ( $elasticaResultSet === null ) {
			throw new RuntimeException( 'Missing Elasticsearch result set' );
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

	protected function runSearch( string $term, ?array $namespaces ) {
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

		$counts = $facetConfig->type === FacetType::LIST ? $valueCounter->countValues( $constraints ) : null;

		$propertyId = $facetConfig->propertyId;
		$propertySerialization = $propertyId->getSerialization();

		$facetData = [
			'property' => $propertySerialization,
			'type' => $facetConfig->type->value,
			'label' => $labelLookup->getLabel( $propertyId )?->getText() ?? $propertySerialization,
			'values' => [],
		];

		foreach ( $counts?->asArray() ?? [] as $valCount ) {
			$facetData['values'][] = [
				'value' => $valCount->value,
				'count' => $valCount->count,
				'label' => $formatter->getLabel( (string)$valCount->value, $propertyId ),
			];
		}

		return $facetData;
	}
}
