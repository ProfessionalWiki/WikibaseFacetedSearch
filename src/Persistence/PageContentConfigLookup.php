<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use JsonContent;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigLookup;

class PageContentConfigLookup implements ConfigLookup {

	public function __construct(
		private readonly PageContentFetcher $contentFetcher,
		private readonly ConfigDeserializer $deserializer,
		private readonly string $pageName
	) {
	}

	public function getConfig(): Config {
		$content = $this->contentFetcher->getPageContent( 'MediaWiki:' . $this->pageName );

		if ( $content instanceof \JsonContent ) {
			return $this->configFromJsonContent( $content );
		}

		return new Config();
	}

	private function configFromJsonContent( JsonContent $content ): Config {
		return $this->deserializer->deserialize( $content->getText() );
	}

}
