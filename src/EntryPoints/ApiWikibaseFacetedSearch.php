<?php

declare(strict_types=1);

namespace ProfessionalWiki\WikibaseFacetedSearch\EntryPoints;

use ApiBase;
use CirrusSearch\Search\CirrusSearchResultSet;
use Elastica\Query\AbstractQuery;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

class ApiWikibaseFacetedSearch extends ApiBase
{

    public function execute()
    {
        try {
            $params = $this->extractRequestParams();
            $term = $params['search'];
            $namespaces = $params['namespaces'];

            $facets = $this->getFacets($term, $namespaces);

            $this->getResult()->addValue(null, $this->getModuleName(), $facets);
        } catch (\Throwable $e) {
            $this->getResult()->addValue(null, 'error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    private function getFacets(string $term, ?array $namespaces): array
    {
        $searchEngine = MediaWikiServices::getInstance()->newSearchEngine();
        // We limit to 0 because we only need the query structure, but CirrusSearch might need 1 or execution.
        // However, ElasticValueCounter needs the original query.
        // SearchEngine::searchText executes the query.
        $searchEngine->setLimitOffset(1);
        if ($namespaces) {
            $searchEngine->setNamespaces($namespaces);
        }
        $results = $searchEngine->searchText($term);

        if ($results instanceof Status) {
            if (!$results->isOK()) {
                return [];
            }
            $results = $results->getValue();
        }

        if (!$results instanceof CirrusSearchResultSet) {
            return [];
        }

        $query = $results->getElasticaResultSet()->getQuery()->getQuery();

        if (is_array($query)) {
            $queryArr = $query;
            $query = new class ($queryArr) extends AbstractQuery {
                public function __construct(private array $arr)
                {
                }
                public function toArray(): array
                {
                    return $this->arr;
                }
            };
        }

        $extension = WikibaseFacetedSearchExtension::getInstance();
        $queryStringParser = $extension->getQueryStringParser();
        $parsedQuery = $queryStringParser->parse($term);

        $itemType = $parsedQuery->getItemTypes()[0] ?? null;
        if (!$itemType) {
            return [];
        }

        $config = $extension->getConfig();
        $facetConfigs = $config->getFacetConfigForItemType($itemType);

        $data = [];
        $valueCounter = $extension->getValueCounter($query);
        $labelLookup = $extension->getLabelLookup($this->getLanguage());
        $formatter = $extension->getFacetValueFormatter($this->getLanguage());

        foreach ($facetConfigs as $facetConfig) {
            $constraints = $parsedQuery->getConstraintsForProperty($facetConfig->propertyId)
                ?? new PropertyConstraints($facetConfig->propertyId);

            $counts = $valueCounter->countValues($constraints);

            $facetData = [
                'property' => $facetConfig->propertyId->getSerialization(),
                'label' => $labelLookup->getLabel($facetConfig->propertyId)?->getText() ?? $facetConfig->propertyId->getSerialization(),
                'values' => []
            ];

            foreach ($counts->asArray() as $valCount) {
                $facetData['values'][] = [
                    'value' => $valCount->value,
                    'count' => $valCount->count,
                    'label' => $formatter->getLabel((string) $valCount->value, $facetConfig->propertyId)
                ];
            }
            $data[] = $facetData;
        }

        return $data;
    }

    public function getAllowedParams()
    {
        return [
            'search' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
            ],
            'namespaces' => [
                ApiBase::PARAM_TYPE => 'integer',
                ApiBase::PARAM_ISMULTI => true,
            ],
        ];
    }
}
