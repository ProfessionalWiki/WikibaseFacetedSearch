<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\PropertyId;

class Config {

	public function __construct(
		public readonly ?string $linkTargetSitelinkSiteId = null,
		public readonly ?PropertyId $instanceOfId = null,
		public readonly ?FacetConfigList $facets = null
	) {
	}

	public function combine( Config $config ): self {
		return new Config(
			$config->linkTargetSitelinkSiteId ?? $this->linkTargetSitelinkSiteId,
			$config->instanceOfId ?? $this->instanceOfId,
			$config->facets ?? $this->facets
		);
	}

}
