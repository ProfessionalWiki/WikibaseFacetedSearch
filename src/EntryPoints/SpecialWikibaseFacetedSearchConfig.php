<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\EntryPoints;

use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

class SpecialWikibaseFacetedSearchConfig extends SpecialPage {

	public function __construct() {
		parent::__construct( 'WikibaseFacetedSearchConfig' );
	}

	public function execute( $subPage ): void {
		parent::execute( $subPage );

		$title = Title::newFromText( WikibaseFacetedSearchExtension::CONFIG_PAGE_TITLE, NS_MEDIAWIKI );

		if ( $title instanceof Title ) {
			$this->getOutput()->redirect( $title->getFullURL() );
		}
	}

	public function getGroupName(): string {
		return 'wikibase';
	}

	public function getDescription(): Message {
		return $this->msg( 'special-wikibase-faceted-search-config' );
	}

}
