<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use IContextSource;

class ExportConfigEditPageTextBuilder {

	public function __construct(
		private IContextSource $context
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
		<h2 id="LinkTargetSitelinkSiteId">Link target sitelink site ID</h2>

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
	"linkTargetSitelinkSiteId": "enwiki"
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
		$example = file_get_contents( __DIR__ . '/../../example.json' );

		if ( !is_string( $example ) ) {
			return '';
		}

		return $example;
	}

}
