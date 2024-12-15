<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigLookup;

/**
 * Combines these config sources, with the latter overriding the former:
 * * Defaults
 * * $baseConfig (LocalSettings.php)
 * * ConfigLookup (MediaWiki:WikibaseFacetedSearch)
 */
class CombiningConfigLookup implements ConfigLookup {

	public function __construct(
		private readonly string $baseConfig,
		private readonly ConfigDeserializer $deserializer,
		private readonly ConfigLookup $configLookup,
		private readonly bool $enableWikiConfig
	) {
	}

	public function getConfig(): Config {
		$config = $this->createDefaultConfig()->combine(
			$this->deserializer->deserialize( $this->baseConfig )
		);

		if ( !$this->enableWikiConfig ) {
			return $config;
		}

		return $config->combine( $this->configLookup->getConfig() );
	}

	private function createDefaultConfig(): Config {
		return new Config();
	}
}
