# Wikibase Faceted Search

[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/ProfessionalWiki/WikibaseFacetedSearch/ci.yml?branch=master)](https://github.com/ProfessionalWiki/WikibaseFacetedSearch/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/ProfessionalWiki/WikibaseFacetedSearch/branch/master/graph/badge.svg)](https://codecov.io/gh/ProfessionalWiki/WikibaseFacetedSearch)
[![Latest Stable Version](https://poser.pugx.org/professional-wiki/wikibase-faceted-search/v/stable)](https://packagist.org/packages/professional-wiki/wikibase-faceted-search)
[![Download count](https://poser.pugx.org/professional-wiki/wikibase-faceted-search/downloads)](https://packagist.org/packages/professional-wiki/wikibase-faceted-search)
[![License](https://poser.pugx.org/professional-wiki/wikibase-faceted-search/license)](LICENSE)

Wikibase Faceted Search enhances MediaWiki's search with faceted search capabilities. Filter results based on instance type or statement values.

- [Introduction to the extension](https://professional.wiki/en/extension/wikibase-faceted-search#Overview)
- [Usage documentation](https://professional.wiki/en/extension/wikibase-faceted-search#Usage)
- [Installation](https://professional.wiki/en/extension/wikibase-faceted-search#Installation)
- [Configuration](https://professional.wiki/en/extension/wikibase-faceted-search#Configuration)
- [Development](#development)
- [Release notes](#release-notes)

Get professional support for this extension via [Professional Wiki], its creators and maintainers.
We provide [MediaWiki Development], [MediaWiki Hosting], and [MediaWiki Consulting] services.

## Demo

Quickly get an idea about what this extension does by checking out the [demo video](https://www.youtube.com/watch?v=CxKWpTQBrqk)
or [MaRDI Portal search](https://portal.mardi4nfdi.de/wiki/Special:Search).

## Development

Run `composer install` in `extensions/WikibaseFacetedSearch/` to make the code quality tools available.

### Running Tests and CI Checks

You can use the `Makefile` by running make commands in the `WikibaseFacetedSearch` directory.

Commands to run in a MediaWiki environment/container:

* `make` or `make ci`: Run everything
* `make test`: Run all PHP tests
* `make phpunit --filter FooBar`: run only PHPUnit tests with FooBar in their name
* `make cs`: Run PHP style checks and static analysis
* `make phpcs`: Run PHP style checks
* `make stan`: Run PHP static analysis
* `make stan-baseline`: Update the PHPStan baseline file (which contains errors we wish to ignore)

Commands that use Docker:

* `make jest` Run JS tests
* `make lint` Lint JS, CSS, and i18n files
* `make js` Run all JS checks

## Release Notes

### Version 1.1.0 - 2026-06-15

* Added support for the external identifier property type to list facets
* Added support for the EDTF property type to list facets (range facets are not yet supported)
* Added support for MediaWiki 1.45
* Added support for PHP 8.5
* Updated translations

### Version 1.0.1 - 2025-12-05

* Updated translations

### Version 1.0.0 - 2025-06-10

* Added tab and sidebar-based facet UI to `Special:Search`
* Added range facets for numerical and date values
* Added list facets with available values listed by occurrence counts
* Added special support for values of type Wikibase Item to list facets
* Added support for "any of", "all of", "no value", "any value" to list facets
* Added a mobile version of the facet UI
* Added comprehensive Elasticsearch indexing of Wikibase values
* Added support for attaching indexed Wikibase values to a normal wiki page for combined full-text and structured queries
* Added on-wiki configuration UI at `MediaWiki:WikibaseFacetedSearch`
* Compatibility with MediaWiki 1.43 up to (at least) 1.44
* Compatibility with PHP 8.1 up to (at least) 8.4

[Professional Wiki]: https://professional.wiki
[MediaWiki Hosting]: https://pro.wiki
[MediaWiki Development]: https://professional.wiki/en/mediawiki-development
[MediaWiki Consulting]: https://professional.wiki/en/mediawiki-consulting-services

## Facet API (fork)

Use the same search expression for both requests:

- Results: `action=query&list=search&srsearch=haswbfacet:P1=Q1&srnamespace=120`.
- Facet metadata/counts: `action=wbfacetsearch&search=haswbfacet:P1=Q1&namespaces=120&format=json&formatversion=2`.

The facet response is an array under `wbfacetsearch`. Each entry contains `property`, `label`, `type`, and `values`; list values contain `value`, `label`, and `count`. Range facets return metadata with an empty values array rather than aggregating individual dates. List counts use the existing upstream counter (top 100 values).

The first item-type token selects the facet configuration. Repeating item-type tokens searches the union of those exact types; the extension does not traverse subclass relationships. Consumers that expand classes must keep the desired configuration type first. Configure facet properties for each concrete item type before rebuilding the index: indexing selects properties using the item's first best-ranked type statement.

Missing type and missing facet configuration return `wbfs-item-type-required` and `wbfs-no-facets` API errors. Search failures return `wbfs-search-failed`; internal details are logged on the server and are not returned to callers. Empty value buckets remain a valid result. Clients must check MediaWiki's JSON `error` field even when HTTP status is 200.

After configuration changes, rebuild with the upstream installation commands:

```sh
php maintenance/run.php CirrusSearch:UpdateSearchIndexConfig
php maintenance/run.php CirrusSearch:ForceSearchIndex --skipParse
php maintenance/run.php CirrusSearch:ForceSearchIndex --skipLinks --indexOnSkip
```

Run `make phpunit --filter ApiWikibaseFacetedSearchTest` inside the MediaWiki test environment to exercise the API error paths. The normal upstream CI suite remains required for release.
