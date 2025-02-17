<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use MediaWiki\User\UserIdentity;
use Wikibase\DataModel\Entity\Item;

interface ItemPageUpdater {

	public function updatePage( Item $item, UserIdentity $user ): void;

}
