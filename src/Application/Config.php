<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

class Config {

	public function __construct(
		public readonly ?string $linkTargetSitelinkSiteId = null
	) {
	}

	public function combine( Config $config ): self {
		return new Config(
			$config->linkTargetSitelinkSiteId ?? $this->linkTargetSitelinkSiteId
		);
	}

}
