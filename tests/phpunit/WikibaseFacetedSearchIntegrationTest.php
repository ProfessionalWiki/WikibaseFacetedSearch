<?php

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests;

use Article;
use MediaWiki\EditPage\EditPage;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\Property;
use Wikibase\Repo\Content\ItemContent;
use Wikibase\Repo\Content\PropertyContent;
use WikiPage;

class WikibaseFacetedSearchIntegrationTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		WikibaseFacetedSearchExtension::getInstance()->clearConfig();
	}

	protected function editConfigPage( string $config ): void {
		$this->editPage(
			'MediaWiki:' . WikibaseFacetedSearchExtension::CONFIG_PAGE_TITLE,
			$config
		);
	}

	protected function deleteConfigPage(): void {
		/**
		 * @var Title $title
		 */
		$title = Title::newFromText( 'MediaWiki:' . WikibaseFacetedSearchExtension::CONFIG_PAGE_TITLE );

		$this->deletePage( new WikiPage( $title ) );
	}

	protected function getPageHtml( string $pageTitle ): string {
		$title = Title::newFromText( $pageTitle );

		$article = new Article( $title, 0 );
		$article->getContext()->getOutput()->setTitle( $title );

		$article->view();

		return $article->getContext()->getOutput()->getHTML();
	}

	protected function getEditPageHtml( string $pageTitle ): string {
		$title = Title::newFromText( $pageTitle );

		$article = new Article( $title, 0 );
		$article->getContext()->getOutput()->setTitle( $title );

		$editPage = new EditPage( $article );
		$editPage->setContextTitle( $title );
		$editPage->getContext()->setUser( $this->getTestSysop()->getUser() );
		$editPage->edit();

		return $editPage->getContext()->getOutput()->getHTML();
	}

	protected function createPage( string $title = 'Test page', int $namespace = NS_MAIN ): WikiPage {
		$page = new WikiPage( Title::newFromText( $title, $namespace ) );
		$this->editPage( $page, 'Wikitext content' );
		return $page;
	}

	protected function createItemPage( Item $item ): WikiPage {
		$page = new WikiPage( Title::newFromText( $item->getId()->getSerialization(), WB_NS_ITEM ) );
		$content = ItemContent::newFromItem( $item );
		$this->editPage( $page, $content );
		return $page;
	}

	protected function createPropertyPage( Property $property ): WikiPage {
		$page = new WikiPage( Title::newFromText( $property->getId()->getSerialization(), WB_NS_PROPERTY ) );
		$content = PropertyContent::newFromProperty( $property );
		$this->editPage( $page, $content );
		return $page;
	}

}
