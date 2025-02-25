<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\User\User;

class ConfigAuthorizer {

	public function __construct(
		private readonly bool $enableWikiConfig,
		private readonly User $user
	) {
	}

	public function isAuthorized( ProperPageIdentity $page ): bool {
		if ( !$this->enableWikiConfig ) {
			return false;
		}

		return $this->user->probablyCan( 'edit', $page );
	}

}
