<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use PHPUnit\Framework\TestCase;
use MediaWiki\MediaWikiServices;
use ProfessionalWiki\WikibaseFacetedSearch\Application\LocalizedTextLookup;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\LocalizedTextLookup
 */
class LocalizedTextLookupTest extends TestCase {

	public function testGetLabelFromEntityIdString() {
		$lookup = $this->newLocalizedTextLookup();

		$itemId = new ItemId( 'Q1' );
		$this->setLabelToEntity( $itemId, 'Test label' );

		$this->assertSame( 'test', $lookup->getLabelFromEntityIdString( 'Q1' ) );
	}

	public function testGetLabelFromEntityId() {
		$lookup = $this->newLocalizedTextLookup();

		$itemId = new ItemId( 'Q1' );
		$this->setLabelToEntity( $itemId, 'Test label' );

		$this->assertSame( 'Test label', $lookup->getLabelFromEntityId( $itemId ) );
	}

	private function setLabelToEntity( ItemId $itemId, string $label ) {
		return ( new Item( $itemId ) )->setLabel(
			MediaWikiServices::getInstance()->getContentLanguageCode()->toString(),
			$label
		);
	}

	private function newLocalizedTextLookup(): LocalizedTextLookup {
		return new LocalizedTextLookup(
			entityIdParser: WikibaseFacetedSearchExtension::getInstance()->getEntityIdParser(),
			labelLookup: WikibaseFacetedSearchExtension::getInstance()->getLabelLookup( MediaWikiServices::getInstance()->getContentLanguage() )
		);
	}
}
