<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\EntryPoints;

use CirrusSearch\CirrusSearch;
use CirrusSearch\Maintenance\MappingConfigBuilder;
use CirrusSearch\Query\KeywordFeature;
use CirrusSearch\Search\NestedIndexField;
use CirrusSearch\SearchConfig;
use ContentHandler;
use EditPage;
use Html;
use HtmlArmor;
use IContextSource;
use Language;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use OutputPage;
use ParserOutput;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigJsonValidator;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\HasWbFacetFeature;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\ConfigJsonErrorFormatter;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\ExportConfigEditPageTextBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use SearchEngine;
use SearchIndexField;
use SearchResult;
use Skin;
use SpecialSearch;
use Title;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\Lookup\InProcessCachingDataTypeLookup;
use Wikibase\DataModel\Term\TermFallback;
use Wikibase\Lib\EntityTypeDefinitions;
use Wikibase\Lib\SettingsArray;
use Wikibase\Lib\Store\LanguageFallbackLabelDescriptionLookup;
use Wikibase\Repo\Hooks\Formatters\EntityLinkFormatter;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Search\Elastic\EntityResult;
use Wikibase\Search\Elastic\Fields\DescriptionsProviderFieldDefinitions;
use Wikibase\Search\Elastic\Fields\ItemFieldDefinitions;
use Wikibase\Search\Elastic\Fields\LabelsProviderFieldDefinitions;
use Wikibase\Search\Elastic\Fields\StatementProviderFieldDefinitions;
use Wikibase\Search\Elastic\WikibaseSearchConfig;
use WikiPage;
use WikitextContentHandler;

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
		$itemId = self::getItemId( $title );

		if ( $itemId === null ) {
			return;
		}

		$pageTitle = self::getItemPage( $itemId );

		if ( $pageTitle === null ) {
			return;
		}

		$title = $pageTitle;

		if ( !( $result instanceof EntityResult ) ) {
			self::rewriteLinkForNonEntityResult(
				self::newLabelDescriptionLookup( $specialSearch->getContext() ),
				self::newLinkFormatter( $specialSearch->getLanguage() ),
				$itemId,
				$titleSnippet,
				$attributes
			);
		}
	}

	private static function getItemId( Title $title ): ?ItemId {
		$entityId = WikibaseRepo::getEntityIdLookup()->getEntityIdForTitle( $title );

		if ( $entityId instanceof ItemId ) {
			return $entityId;
		}

		return null;
	}

	private static function getItemPage( ItemId $itemId ): ?Title {
		return WikibaseFacetedSearchExtension::getInstance()->getItemPageLookup()->getPageTitle( $itemId );
	}

	private static function newLinkFormatter( Language $language ): EntityLinkFormatter {
		return WikibaseRepo::getEntityLinkFormatterFactory()->getDefaultLinkFormatter( $language );
	}

	private static function newLabelDescriptionLookup( IContextSource $context ): LanguageFallbackLabelDescriptionLookup {
		return new LanguageFallbackLabelDescriptionLookup(
			WikibaseRepo::getTermLookup(),
			WikibaseRepo::getLanguageFallbackChainFactory()->newFromContext( $context )
		);
	}

	/**
	 * @param string[] $attributes
	 */
	private static function rewriteLinkForNonEntityResult(
		LanguageFallbackLabelDescriptionLookup $labelDescriptionLookup,
		EntityLinkFormatter $linkFormatter,
		ItemId $itemId,
		string|HtmlArmor|null &$titleSnippet,
		array &$attributes
	): void {
		$labelData = self::termFallbackToTermData(
			$labelDescriptionLookup->getLabel( $itemId )
		);
		$descriptionData = self::termFallbackToTermData(
			$labelDescriptionLookup->getDescription( $itemId )
		);

		$titleSnippet = new HtmlArmor( $linkFormatter->getHtml( $itemId, $labelData ) );

		$attributes['title'] = $linkFormatter->getTitleAttribute(
			$itemId,
			$labelData,
			$descriptionData
		);
	}

	/**
	 * @return string[]|null
	 */
	private static function termFallbackToTermData( ?TermFallback $term = null ): ?array {
		if ( $term ) {
			return [
				'value' => $term->getText(),
				'language' => $term->getActualLanguageCode(),
			];
		}

		return null;
	}

	public static function onSpecialSearchResultsAppend(
		SpecialSearch $specialSearch,
		OutputPage $output,
		string $term
	): void {
		// TODO: generate facets from search term
		$output->addModuleStyles( 'ext.wikibase.facetedsearch.styles' );
		$output->addHTML(
			WikibaseFacetedSearchExtension::getInstance()->newFacetUiBuilder()->createHtml( /* TODO: FacetList */ )
		);
	}

	public static function onContentHandlerDefaultModelFor( Title $title, ?string &$model ): void {
		if ( WikibaseFacetedSearchExtension::getInstance()->isConfigTitle( $title ) ) {
			$model = CONTENT_MODEL_JSON;
		}
	}

	public static function onEditFilter( EditPage $editPage, ?string $text, ?string $section, string &$error ): void {
		$validator = ConfigJsonValidator::newInstance();

		if ( is_string( $text )
			&& WikibaseFacetedSearchExtension::getInstance()->isConfigTitle( $editPage->getTitle() )
			&& !$validator->validate( $text )
		) {
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

			$textBuilder = new ExportConfigEditPageTextBuilder( $editPage->getContext() );
			$editPage->editFormTextTop = $textBuilder->createTopHtml();
			$editPage->editFormTextBottom = $textBuilder->createBottomHtml();

			$editPage->getContext()->getOutput()->addModuleStyles( [ 'ext.wikibase.facetedsearch.docs.styles' ] );
		}
	}

	public static function onEditFormPreloadText( string &$text, Title &$title ): void {
		if ( WikibaseFacetedSearchExtension::getInstance()->isConfigTitle( $title ) ) {
			$text = trim( '
{
	"linkTargetSitelinkSiteId": null,
	"instanceOfId": null,
	"facets": {}
}' );
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
		$extraFeatures[] = new HasWbFacetFeature();
	}

	public static function onWikibaseRepoEntityTypes( array &$entityTypeDefinitions ) {
		$wbcsConfig = self::getWBCSConfig();
		if ( !$wbcsConfig->enabled() ) {
			return;
		}

		// TODO: This *overwrites* the config set by WikibaseCirrusSearch, but includes the original config.
		$entityTypeDefinitions['item'][EntityTypeDefinitions::SEARCH_FIELD_DEFINITIONS] = static function ( array $languageCodes, SettingsArray $searchSettings ) {
			$configFactory = MediaWikiServices::getInstance()->getConfigFactory();
			return new ItemFieldDefinitions( [
				new LabelsProviderFieldDefinitions( $languageCodes, $configFactory ),
				new DescriptionsProviderFieldDefinitions( $languageCodes, $configFactory ),
				StatementProviderFieldDefinitions::newFromSettings(
					new InProcessCachingDataTypeLookup( WikibaseRepo::getPropertyDataTypeLookup() ),
					WikibaseRepo::getDataTypeDefinitions()->getSearchIndexDataFormatterCallbacks(),
					$searchSettings,
					WikibaseRepo::getLogger()
				),
				\ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Field\StatementProviderFieldDefinitions::newFromSettings(
					new InProcessCachingDataTypeLookup( WikibaseRepo::getPropertyDataTypeLookup() ),
					WikibaseRepo::getDataTypeDefinitions()->getSearchIndexDataFormatterCallbacks(),
					$searchSettings,
					WikibaseRepo::getLogger()
				)
			] );
		};
	}

	private static function getWBCSConfig(): WikibaseSearchConfig {
		return MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'WikibaseCirrusSearch' );
	}

	/**
	 * @param SearchIndexField[] $fields
	 */
	public static function onSearchIndexFields( array &$fields, SearchEngine $engine ): void {
		if ( !( $engine instanceof CirrusSearch ) ) {
			return;
		}

		// TODO: dedicated field
		$fields['wbfs_P1_alt'] = $engine->makeSearchFieldMapping( 'wbfs_P1_alt', SearchIndexField::INDEX_TYPE_NUMBER );

		// TODO: nested approach
		/** @var NestedIndexField $facetsField */
		$facetsField = $engine->makeSearchFieldMapping( 'wbfs_facets', SearchIndexField::INDEX_TYPE_NESTED );
		// TODO: these "keyword" types are still ending up as "text" type
		$facetsField->addSubfield( 'property', $engine->makeSearchFieldMapping( 'property', SearchIndexField::INDEX_TYPE_KEYWORD ) );
		$facetsField->addSubfield( 'value_type', $engine->makeSearchFieldMapping( 'value_type', SearchIndexField::INDEX_TYPE_KEYWORD ) );
		$facetsField->addSubfield( 'value_keyword', $engine->makeSearchFieldMapping( 'value_keyword', SearchIndexField::INDEX_TYPE_KEYWORD ) );
		$facetsField->addSubfield( 'value_number', $engine->makeSearchFieldMapping( 'value_number', SearchIndexField::INDEX_TYPE_NUMBER ) );
		$facetsField->addSubfield( 'value_date', $engine->makeSearchFieldMapping( 'value_date', SearchIndexField::INDEX_TYPE_DATETIME ) );
		$fields['wbfs_facets'] = $facetsField;
	}

	public static function onSearchDataForIndex(
		array &$fields,
		ContentHandler $handler,
		WikiPage $page,
		ParserOutput $output,
		SearchEngine $engine
	) {
		if ( !( $handler instanceof WikitextContentHandler ) ) {
			return;
		}
		// TODO: this triggers on all page updates.
	}

}
