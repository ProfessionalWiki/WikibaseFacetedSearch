<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use Elastica\Query\AbstractQuery;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetHtmlBuilder;

class SpyFacetHtmlBuilder implements FacetHtmlBuilder {

	private FacetConfig $config;
	private PropertyConstraints $state;
	private AbstractQuery $currentQuery;

	public function buildHtml( FacetConfig $config, PropertyConstraints $state, AbstractQuery $query ): string {
		$this->config = $config;
		$this->state = $state;
		$this->currentQuery = $query;
		return '';
	}

	public function getConfig(): FacetConfig {
		return $this->config;
	}

	public function getState(): PropertyConstraints {
		return $this->state;
	}

	public function getCurrentQuery(): AbstractQuery {
		return $this->currentQuery;
	}

}
