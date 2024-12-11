<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfigList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

class Valid {

	public static function configJson(): string {
		return '
{
    "linkTargetSitelinkSiteId": "enwiki",
    "instanceOfId": "P42",
    "facets": {
		"Q100": [
			{
				"property": "P100",
				"type": "boolean"
			},
			{
				"property": "P100",
				"type": "list"
			},
			{
				"property": "P200",
				"type": "range"
			}
		],
		"Q200": [
			{
				"property": "P300",
				"type": "list"
			}
		]
	}
}
';
	}

	public static function config(): Config {
		return new Config(
			linkTargetSitelinkSiteId: 'enwiki',
			instanceOfId: new NumericPropertyId( 'P42' ),
			facets: new FacetConfigList(
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P100' ), FacetType::BOOLEAN ),
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P100' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P200' ), FacetType::RANGE ),
				new FacetConfig( new ItemId( 'Q200' ), new NumericPropertyId( 'P300' ), FacetType::LIST )
			)
		);
	}

}
