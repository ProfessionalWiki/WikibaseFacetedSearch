<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\User\User;

class UserBasedConfigAuthorizer implements ConfigAuthorizer {

	public function __construct(
		private readonly bool $wikiConfigIsEnabled,
		private readonly User $user
	) {
	}

	public function isAuthorized( ProperPageIdentity $page ): bool {
		if ( !$this->wikiConfigIsEnabled ) {
			return false;
		}

		return $this->user->probablyCan( 'edit', $page );
	}

} 