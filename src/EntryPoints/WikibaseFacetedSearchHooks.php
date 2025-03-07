<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\EntryPoints;

use Action;
use CirrusSearch\CirrusSearch;
use CirrusSearch\Query\KeywordFeature;
use CirrusSearch\Search\CirrusSearchResultSet;
use CirrusSearch\SearchConfig;
use Elastica\Query\AbstractQuery;
use HtmlArmor;
use ISearchResultSet;
use MediaWiki\Content\ContentHandler;
use MediaWiki\EditPage\EditPage;
use MediaWiki\Html\Html;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Specials\SpecialSearch;
use MediaWiki\Storage\EditResult;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\ConfigJsonErrorFormatter;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use SearchEngine;
use SearchIndexField;
use SearchResult;
use Skin;
use Wikibase\Repo\Content\ItemContent;
use WikiPage;

class WikibaseFacetedSearchHooks {

	/**
	 * @param string[] $terms
	 * @param string[] $query
	 * @param string[] $attributes
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
		if ( !WikibaseFacetedSearchExtension::getInstance()->getConfig()->isComplete() ) {
			return;
		}

		$itemId = WikibaseFacetedSearchExtension::getInstance()->getPageItemLookup()->getItemId( $result->getTitle() );

		if ( $itemId === null ) {
			return;
		}

		$titleSnippet = WikibaseFacetedSearchExtension::getInstance()
			->getLabelLookup( $specialSearch->getLanguage() )
			->getLabel( $itemId )
			?->getText() ?? $titleSnippet;
	}

	public static function onSpecialSearchResultsPrepend(
		SpecialSearch $specialSearch,
		OutputPage $output,
		string $term
	): void {
		if ( !WikibaseFacetedSearchExtension::getInstance()->getConfig()->isComplete() ) {
			return;
		}

		$output->addModuleStyles( 'ext.wikibase.facetedsearch.styles' );
		$output->addModules( 'ext.wikibase.facetedsearch' );

		$output->addHTML(
			WikibaseFacetedSearchExtension::getInstance()->getTabsHtmlBuilder(
				language: $specialSearch->getLanguage(),
				user: $output->getUser()
			)->createHtml(
				searchQuery: $term
			)
		);
	}

	public static function onSpecialSearchResults(
		string $term,
		?ISearchResultSet $titleMatches,
		?ISearchResultSet $textMatches
	): void {
		if ( !WikibaseFacetedSearchExtension::getInstance()->getConfig()->isComplete() ) {
			return;
		}

		if ( $textMatches instanceof CirrusSearchResultSet ) {
			self::setCurrentQuery( $textMatches->getElasticaResultSet()->getQuery()->getQuery() );
		}
	}

	private static function setCurrentQuery( AbstractQuery $query ): void {
		$GLOBALS[WikibaseFacetedSearchExtension::QUERY_GLOBAL] = $query;
	}

	public static function onSpecialSearchResultsAppend(
		SpecialSearch $specialSearch,
		OutputPage $output,
		string $term
	): void {
		if ( !WikibaseFacetedSearchExtension::getInstance()->getConfig()->isComplete() ) {
			return;
		}

		$output->addHTML(
			WikibaseFacetedSearchExtension::getInstance()
				->getSidebarHtmlBuilder(
					language: $specialSearch->getLanguage(),
					currentQuery: self::getFilteredCurrentQuery( $term )
				)
				->createHtml(
					searchQuery: $term
				)
		);
	}

	private static function getFilteredCurrentQuery( string $searchQuery ): AbstractQuery {
		return WikibaseFacetedSearchExtension::getInstance()->newElasticQueryFilter()->removeOrFacets(
			self::getCurrentQuery(),
			WikibaseFacetedSearchExtension::getInstance()->getQueryStringParser()->parse( $searchQuery )
		);
	}

	private static function getCurrentQuery(): AbstractQuery {
		return $GLOBALS[WikibaseFacetedSearchExtension::QUERY_GLOBAL];
	}

	public static function onContentHandlerDefaultModelFor( Title $title, ?string &$model ): void {
		if ( WikibaseFacetedSearchExtension::getInstance()->isConfigTitle( $title ) ) {
			$model = CONTENT_MODEL_JSON;
		}
	}

	public static function onEditFilter( EditPage $editPage, ?string $text, ?string $section, string &$error ): void {
		if ( !is_string( $text ) || !WikibaseFacetedSearchExtension::getInstance()->isConfigTitle( $editPage->getTitle() ) ) {
			return;
		}

		$validator = WikibaseFacetedSearchExtension::getInstance()->newConfigJsonValidator();

		if ( !$validator->validate( $text ) ) {
			$errors = $validator->getErrors();
			$error = Html::errorBox(
				wfMessage( 'wikibase-faceted-search-config-invalid', count( $errors ) )->escaped() .
				ConfigJsonErrorFormatter::format( $errors )
			);
		}
	}

	public static function onAlternateEdit( EditPage $editPage ): void {
		if ( WikibaseFacetedSearchExtension::getInstance()->isConfigTitle( $editPage->getTitle() ) ) {
			$editPage->suppressIntro = true;

			$textBuilder = WikibaseFacetedSearchExtension::getInstance()->newConfigDocumentationBuilder( $editPage->getContext() );
			$editPage->editFormTextTop = $textBuilder->createDocumentationLink();
			$editPage->editFormTextBottom = $textBuilder->createDocumentation();

			$editPage->getContext()->getOutput()->addModuleStyles( [ 'ext.wikibase.facetedsearch.docs.styles' ] );
		}
	}

	public static function onEditFormPreloadText( string &$text, Title &$title ): void {
		if ( WikibaseFacetedSearchExtension::getInstance()->isConfigTitle( $title ) ) {
			$text = trim( WikibaseFacetedSearchExtension::DEFAULT_CONFIG );
		}
	}

	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ): void {
		$title = $out->getTitle();

		if ( $title === null ) {
			return;
		}

		if ( !WikibaseFacetedSearchExtension::getInstance()->isConfigTitle( $title ) ) {
			return;
		}

		$html = $out->getHTML();
		$out->clearHTML();
		$out->addHTML( self::getConfigPageHtml( $html ) );

		if ( Action::getActionName( $out->getContext() ) === 'view' ) {
			$out->addHTML(
				WikibaseFacetedSearchExtension::getInstance()->newConfigDocumentationBuilder( $out->getContext() )->createDocumentation()
			);
		}
	}

	private static function getConfigPageHtml( string $html ): string {
		$jsonTablePosition = strpos( $html, '<table class="mw-json">' );

		if ( !$jsonTablePosition ) {
			return $html;
		}

		return substr( $html, $jsonTablePosition );
	}

	/**
	 * @param KeywordFeature[] &$extraFeatures
	 */
	public static function onCirrusSearchAddQueryFeatures( SearchConfig $config, array &$extraFeatures ): void {
		if ( WikibaseFacetedSearchExtension::getInstance()->getConfig()->isComplete() ) {
			$extraFeatures[] = WikibaseFacetedSearchExtension::getInstance()->newHasWbFacetFeature();
		}
	}

	/**
	 * @param SearchIndexField[] $fields
	 */
	public static function onSearchIndexFields( array &$fields, SearchEngine $engine ): void {
		if ( !( $engine instanceof CirrusSearch ) ) {
			return;
		}

		if ( WikibaseFacetedSearchExtension::getInstance()->getConfig()->isComplete() ) {
			$fields = WikibaseFacetedSearchExtension::getInstance()->newSearchIndexFieldsBuilder( $engine )->createFieldObjects()
				+ $fields;
		}
	}

	public static function onSearchDataForIndex2(
		array &$fields,
		ContentHandler $handler,
		WikiPage $page,
		ParserOutput $output,
		SearchEngine $engine,
		RevisionRecord $revision
	): void {
		if ( !( $engine instanceof CirrusSearch ) ) {
			return;
		}

		if ( WikibaseFacetedSearchExtension::getInstance()->getConfig()->isComplete() ) {
			$fields = WikibaseFacetedSearchExtension::getInstance()->newStatementListTranslator()->translateStatements(
					WikibaseFacetedSearchExtension::getInstance()->newStatementsLookup()->getStatements( $page )
				) + $fields;
		}
	}

	public static function onPageSaveComplete(
		WikiPage $wikiPage,
		UserIdentity $user,
		string $summary,
		int $flags,
		RevisionRecord $revisionRecord,
		EditResult $editResult
	) {
		if ( !WikibaseFacetedSearchExtension::getInstance()->getConfig()->isComplete() ) {
			return;
		}

		$content = $wikiPage->getContent();

		if ( !( $content instanceof ItemContent ) ) {
			return;
		}

		WikibaseFacetedSearchExtension::getInstance()->newItemPageUpdater()->updatePage( $content->getItem(), $user );
	}
}
