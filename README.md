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
