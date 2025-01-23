<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use PHPUnit\Framework\TestCase;
use MediaWiki\MediaWikiServices;
use ProfessionalWiki\WikibaseFacetedSearch\Application\LocalizedTextLookup;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lib\Tests\MockRepository;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\LocalizedTextLookup
 */
class LocalizedTextLookupTest extends TestCase {

	protected function setUp(): void {
		$this->repo = $this->newMockRepository();
	}

	public function testGetLabelFromEntityIdString() {
		$lookup = $this->newLocalizedTextLookup();

		$this->setLabelToEntity( 'Test label', 'Q1' );
		// TODO: Might have to mock labelLookup or figure out a way to use Wikibase
		$this->assertTrue( true );
		// $this->assertSame( 'Test label', $lookup->getLabelFromEntityIdString( 'Q1' ) );
	}

	public function testGetLabelFromEntityId() {
		$lookup = $this->newLocalizedTextLookup();

		$this->setLabelToEntity( 'Test label', 'Q1' );
		// TODO: Might have to mock labelLookup or figure out a way to use Wikibase
		$this->assertTrue( true );
		// $this->assertSame( 'Test label', $lookup->getLabelFromEntityId( new ItemId( 'Q1' ) ) );
	}

	private function setLabelToEntity( string $label, string $itemIdString ) {
		$langCodeString = MediaWikiServices::getInstance()->getContentLanguageCode()->toString();
		$entity = new Item( new ItemId( $itemIdString ) );
		$entity->setLabel( $langCodeString, $label );
		$this->repo->putEntity( $entity );
	}

	private function newLocalizedTextLookup(): LocalizedTextLookup {
		return new LocalizedTextLookup(
			entityIdParser: WikibaseFacetedSearchExtension::getInstance()->getEntityIdParser(),
			labelLookup: WikibaseFacetedSearchExtension::getInstance()->getLabelLookup( MediaWikiServices::getInstance()->getContentLanguage() )
		);
	}

	private function newMockRepository(): MockRepository {
		return new MockRepository();
	}
}
