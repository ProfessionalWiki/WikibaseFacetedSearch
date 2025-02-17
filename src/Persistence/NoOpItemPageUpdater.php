<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use MediaWiki\User\UserIdentity;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemPageUpdater;
use Wikibase\DataModel\Entity\Item;

class NoOpItemPageUpdater implements ItemPageUpdater {

	public function updatePage( Item $item, UserIdentity $user ): void {
		// Do nothing.
	}

}
