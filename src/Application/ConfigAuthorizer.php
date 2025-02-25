<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use MediaWiki\Page\PageIdentity;

interface ConfigAuthorizer {

	public function isAuthorized( PageIdentity $page ): bool;

}
