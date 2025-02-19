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

	public const ITEM_TYPE_WITH_FACETS = 'Q100';

	public static function configJson(): string {
		return '
{
    "linkTargetSitelinkSiteId": "enwiki",
    "itemTypeProperty": "P42",
    "configPerItemType": {
        "Q100": {
            "facets": {
                "P1": {
                    "type": "list"
                },
                "P2": {
                    "type": "range"
                }
            }
        },
        "Q200": {
            "facets": {
                "P3": {
                    "type": "list"
                }
            }
        }
    }
}

';
	}

	public static function config(): Config {
		return new Config(
			linkTargetSitelinkSiteId: 'enwiki',
			itemTypeProperty: new NumericPropertyId( 'P42' ),
			facets: new FacetConfigList(
				new FacetConfig( new ItemId( self::ITEM_TYPE_WITH_FACETS ), new NumericPropertyId( 'P1' ), FacetType::LIST ),
				new FacetConfig( new ItemId( self::ITEM_TYPE_WITH_FACETS ), new NumericPropertyId( 'P2' ), FacetType::RANGE ),
				new FacetConfig( new ItemId( 'Q200' ), new NumericPropertyId( 'P3' ), FacetType::LIST )
			)
		);
	}

}
