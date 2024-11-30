<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

class Valid {

	public static function configJson(): string {
		return '
{
    "linkTargetSitelinkSiteId": "enwiki"
}
';
	}

}
