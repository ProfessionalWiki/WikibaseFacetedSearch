<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use MediaWiki\Page\ProperPageIdentity;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigAuthorizer;

class StubConfigAuthorizer implements ConfigAuthorizer {

	public function __construct(
		private readonly bool $isAuthorized = false
	) {
	}

	public function isAuthorized( ProperPageIdentity $page ): bool {
		return $this->isAuthorized;
	}

} 