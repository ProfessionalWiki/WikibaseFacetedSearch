<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use Content;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\MalformedTitleException;
use MediaWiki\Title\TitleParser;

class PageContentFetcher {

	public function __construct(
		private readonly TitleParser $titleParser,
		private readonly RevisionLookup $revisionLookup
	) {
	}

	public function getPageContent( string $pageTitle ): ?Content {
		try {
			$title = $this->titleParser->parseTitle( $pageTitle );
		} catch ( MalformedTitleException ) {
			return null;
		}

		$revision = $this->revisionLookup->getRevisionByTitle( $title );

		return $revision?->getContent( SlotRecord::MAIN );
	}

}
