<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Permissions\PermissionStatus;
use MediaWiki\User\User;

class StubUser extends User {

	public function __construct(
		private readonly bool $probablyCan = false
	) {
	}

	public function probablyCan(
		string $action,
		PageIdentity $target,
		?PermissionStatus $status = null,
		array $ignoreErrors = []
	): bool {
		return $this->probablyCan;
	}

} 