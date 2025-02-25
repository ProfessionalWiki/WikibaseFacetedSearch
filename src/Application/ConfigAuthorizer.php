<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use MediaWiki\Page\ProperPageIdentity;

interface ConfigAuthorizer {

	public function isAuthorized( ProperPageIdentity $page ): bool;

}
