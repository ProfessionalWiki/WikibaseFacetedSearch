# Wikibase Faceted Search

[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/ProfessionalWiki/WikibaseFacetedSearch/ci.yml?branch=master)](https://github.com/ProfessionalWiki/WikibaseFacetedSearch/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/ProfessionalWiki/WikibaseFacetedSearch/branch/master/graph/badge.svg)](https://codecov.io/gh/ProfessionalWiki/WikibaseFacetedSearch)
[![Latest Stable Version](https://poser.pugx.org/professional-wiki/wikibase-faceted-search/v/stable)](https://packagist.org/packages/professional-wiki/wikibase-faceted-search)
[![Download count](https://poser.pugx.org/professional-wiki/wikibase-faceted-search/downloads)](https://packagist.org/packages/professional-wiki/wikibase-faceted-search)
[![License](https://poser.pugx.org/professional-wiki/wikibase-faceted-search/license)](LICENSE)

Wikibase Faceted Search enhances MediaWiki's search with faceted search capabilities. Filter results based on instance type or statement values.

- [Introduction to the extension](TODO)
- [Usage documentation](https://professional.wiki/en/extension/wikibase-faceted-search#Usage)
- [Installation](https://professional.wiki/en/extension/wikibase-faceted-search#Installation)
- [Configuration](https://professional.wiki/en/extension/wikibase-faceted-search#Configuration)
- [Development](#development)
- [Release notes](#release-notes)

Get professional support for this extension via [Professional Wiki], its creators and maintainers.
We provide [MediaWiki Development], [MediaWiki Hosting], and [MediaWiki Consulting] services.

## Development

Run `composer install` in `extensions/WikibaseFacetedSearch/` to make the code quality tools available.

### Running Tests and CI Checks

You can use the `Makefile` by running make commands in the `WikibaseFacetedSearch` directory.

* `make ci`: Run everything
* `make test`: Run all tests
* `make phpunit --filter FooBar`: run only PHPUnit tests with FooBar in their name
* `make phpcs`: Run all style checks
* `make cs`: Run all style checks and static analysis

### Updating Baseline Files

Sometimes PHPStan generates errors or warnings we do not wish to fix.
These can be ignored by adding them to the respective baseline file.
You can update these files with `make stan-baseline`.

## Release Notes

### Version 1.0.0 - TBD

* TODO
* Compatibility with MediaWiki 1.43
* Compatibility with PHP 8.1 up to (at least) 8.3

[Professional Wiki]: https://professional.wiki
[MediaWiki Hosting]: https://pro.wiki
[MediaWiki Development]: https://professional.wiki/en/mediawiki-development
[MediaWiki Consulting]: https://professional.wiki/en/mediawiki-consulting-services
