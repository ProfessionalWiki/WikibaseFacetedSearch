<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\EntryPoints;

use HtmlArmor;
use OutputPage;
use SearchResult;
use SpecialSearch;
use Title;

class WikibaseFacetedSearchHooks {

	/**
	 * @param string[] $terms
	 * @param array<string, mixed> $query
	 * @param array<string, mixed> $attributes
	 */
	public static function onShowSearchHitTitle(
		Title &$title,
		string|HtmlArmor|null &$titleSnippet,
		SearchResult $result,
		array $terms,
		SpecialSearch $specialSearch,
		array &$query,
		array &$attributes
	): void {
		if ( $title->getNamespace() !== WB_NS_ITEM ) {
			return;
		}

		// TODO: get item site link and replace $title
	}

	public static function onSpecialSearchResultsAppend(
		SpecialSearch $specialSearch,
		OutputPage $output,
		string $term
	): void {
		// TODO: generate facets from search term
		$output->addModuleStyles( 'ext.wikibase.facetedsearch.styles' );
		$output->addHTML(
			\Html::element( 'div', [ 'class' => 'wikibase-faceted-search__facets' ] )
		);
	}

}
