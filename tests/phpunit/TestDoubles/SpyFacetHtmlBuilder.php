<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetHtmlBuilder;

class SpyFacetHtmlBuilder implements FacetHtmlBuilder {

	private FacetConfig $config;
	private PropertyConstraints $state;

	public function buildHtml( FacetConfig $config, PropertyConstraints $state ): string {
		$this->config = $config;
		$this->state = $state;
		return '';
	}

	public function getConfig(): FacetConfig {
		return $this->config;
	}

	public function getState(): PropertyConstraints {
		return $this->state;
	}

}
