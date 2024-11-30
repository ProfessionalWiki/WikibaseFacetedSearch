<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigLookup;

class StubConfigLookup implements ConfigLookup {

	public function __construct(
		private Config $config
	) {
	}

	public function getConfig(): Config {
		return $this->config;
	}

}
