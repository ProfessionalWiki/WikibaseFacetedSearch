<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\EntryPoints;

use CirrusSearch\CirrusSearch;
use CirrusSearch\Query\KeywordFeature;
use CirrusSearch\SearchConfig;
use HtmlArmor;
use MediaWiki\Content\ContentHandler;
use MediaWiki\EditPage\EditPage;
use MediaWiki\Html\Html;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Specials\SpecialSearch;
use MediaWiki\Title\Title;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\HasWbFacetFeature;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\ConfigEditPageTextBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\ConfigJsonErrorFormatter;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use SearchEngine;
use SearchIndexField;
use SearchResult;
use Skin;
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
		$output->addModuleStyles( 'ext.wikibase.facetedsearch.styles' );
		$output->addModules( 'ext.wikibase.facetedsearch' );

		$output->addHTML(
			WikibaseFacetedSearchExtension::getInstance()->getUiBuilder( $specialSearch->getLanguage() )->createHtml(
				searchQuery: $term
			)
		);
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

			$textBuilder = new ConfigEditPageTextBuilder(
				context: $editPage->getContext(),
				exampleConfigPath: WikibaseFacetedSearchExtension::getInstance()->getExampleConfigPath()
			);
			$editPage->editFormTextTop = $textBuilder->createTopHtml();
			$editPage->editFormTextBottom = $textBuilder->createBottomHtml();

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

		if ( WikibaseFacetedSearchExtension::getInstance()->isConfigTitle( $title ) ) {
			$html = $out->getHTML();
			$out->clearHTML();
			$out->addHTML( self::getConfigPageHtml( $html ) );
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

	public static function onSearchDataForIndex(
		array &$fields,
		ContentHandler $handler,
		WikiPage $page,
		ParserOutput $output,
		SearchEngine $engine
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

}
