<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\EntryPoints;

use HtmlArmor;
use IContextSource;
use Language;
use OutputPage;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use SearchResult;
use SpecialSearch;
use Title;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Term\TermFallback;
use Wikibase\Lib\Store\LanguageFallbackLabelDescriptionLookup;
use Wikibase\Repo\Hooks\Formatters\EntityLinkFormatter;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Search\Elastic\EntityResult;

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
			\Html::element( 'div', [ 'class' => 'wikibase-faceted-search__facets' ] )
		);
	}

}
