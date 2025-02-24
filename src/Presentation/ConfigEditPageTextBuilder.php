<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Context\IContextSource;

class ConfigEditPageTextBuilder {

	public function __construct(
		private readonly IContextSource $context,
		private readonly string $exampleConfigPath
	) {
	}

	public function createTopHtml(): string {
		return '<div id="wikibase-faceted-search-config-help-top">' .
			$this->createDocumentationLink() .
			'</div>';
	}

	private function createDocumentationLink(): string {
		return '<p>'
			. $this->context->msg( 'wikibase-faceted-search-config-help-documentation' )->parse()
			. '</p>';
	}

	public function createBottomHtml(): string {
		return <<<HTML
<div id="Documentation">
	<section>
		<h2 id="ConfigurationDocumentation">{$this->context->msg( 'wikibase-faceted-search-config-help' )->escaped()}</h2>

		<p>
			Besides the configuration reference below, you can consult the Wikibase Faceted Search
			<a href="https://professional.wiki/en/extension/wikibase-faceted-search">usage documentation</a> and
			<a href="https://facetedsearch.wikibase.wiki">demo wiki</a>.
		</p>
	</section>

	<section>
		<h2 id="sitelinkSiteId">Link target sitelink site ID</h2>

		<p>
			By default search result items link to their item page (<code>Item:Q123</code>).
		</p>

		<p>
			You can change the link to use a sitelink of the item instead.
		</p>

		<p>
			Example configuration:
		</p>

		<pre>
{
	"sitelinkSiteId": "enwiki"
}</pre>
	</section>

	<section>
		<h2 id="ItemTypeProperty">Instance Of property ID</h2>

		<p>
			The property ID for the "instance of" property.
		</p>

		<p>
			Example configuration:
		</p>

		<pre>
{
	"itemTypeProperty": "P1"
}</pre>
	</section>

	<section>
		<h2 id="Facets">Facets</h2>

		<p>
			The avilable facets per item type ("instance of"). Each facet is defined by a property ID and a type.
		</p>

		<p>
			Example configuration:
		</p>

		<pre>
{
	"facets": {
		"Q1": [
			{
				"property": "P1",
				"type": "list"
			},
			{
				"property": "P2",
				"type": "range"
			}
		]
	}
}</pre>
	</section>

	<section>
		<h2 id="FullExample">{$this->context->msg( 'wikibase-faceted-search-config-help-example' )->escaped()}</h2>

		<pre>{$this->getExampleContents()}</pre>
	</section>
</div>
HTML;
	}

	private function getExampleContents(): string {
		$example = file_get_contents( $this->exampleConfigPath );

		if ( !is_string( $example ) ) {
			return '';
		}

		return $example;
	}

}
