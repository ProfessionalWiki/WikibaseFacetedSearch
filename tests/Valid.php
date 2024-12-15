<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests;

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
				"property": "P1",
				"type": "boolean"
			},
			{
				"property": "P1",
				"type": "list"
			},
			{
				"property": "P2",
				"type": "range"
			}
		],
		"Q200": [
			{
				"property": "P3",
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
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P1' ), FacetType::BOOLEAN ),
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P1' ), FacetType::LIST ),
				new FacetConfig( new ItemId( 'Q100' ), new NumericPropertyId( 'P2' ), FacetType::RANGE ),
				new FacetConfig( new ItemId( 'Q200' ), new NumericPropertyId( 'P3' ), FacetType::LIST )
			)
		);
	}

}
