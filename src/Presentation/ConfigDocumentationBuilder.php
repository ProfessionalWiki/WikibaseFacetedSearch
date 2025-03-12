<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Context\IContextSource;
use MediaWiki\Html\TemplateParser;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\Title\TitleFactory;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use RuntimeException;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\Lookup\LabelLookup;

class ConfigDocumentationBuilder {

	public function __construct(
		private readonly IContextSource $context,
		private readonly string $exampleConfigPath,
		private readonly TemplateParser $templateParser,
		private readonly Config $config,
		private readonly TitleFactory $titleFactory,
		private readonly LinkRenderer $linkRenderer,
		private readonly LabelLookup $labelLookup
	) {
	}

	public function createDocumentationLink(): string {
		return $this->templateParser->processTemplate(
			'ConfigEditPageTop',
			[
				'msg-wikibase-faceted-search-config-help-documentation' => $this->context->msg( 'wikibase-faceted-search-config-help-documentation' )->parse()
			]
		);
	}

	public function createDocumentation(): string {
		return $this->templateParser->processTemplate(
			'ConfigurationDocumentation',
			[
				'msg-wikibase-entity-item' => $this->context->msg( 'wikibase-entity-item' )->plain(),
				'msg-wikibase-faceted-search-config-tab-name' => $this->context->msg( 'wikibase-faceted-search-config-tab-name' )->plain(),
				'msg-wikibase-faceted-search-config-tab-name-missing' => $this->context->msg( 'wikibase-faceted-search-config-tab-name-missing' )->plain(),
				'msg-wikibase-faceted-search-config-help-example' => $this->context->msg( 'wikibase-faceted-search-config-help-example' )->escaped(),
				'exampleContents' => $this->getExampleContents(),
				'array-itemTypes' => $this->getItemTypesData(),
				'searchUrl' => $this->getSearchUrl(),
			]
		);
	}

	private function getExampleContents(): string {
		$example = file_get_contents( $this->exampleConfigPath );

		if ( !is_string( $example ) ) {
			return '';
		}

		return $example;
	}

	private function getItemTypesData(): array {
		$itemTypes = $this->config->getItemTypes();
		return array_map(
			fn( ItemId $itemType ) => $this->getItemTypeData( $itemType ),
			$itemTypes
		);
	}

	private function getItemTypeData( ItemId $itemType ): array {
		$id = $itemType->getSerialization();
		$tabNameMsg = $this->context->msg( "WikibaseFacetedSearch-item-type-$id" );
		$exists = $tabNameMsg->exists();

		return [
			'id' => $id,
			'exists' => $exists,
			'itemLink' => $this->getItemLink( $id, $this->labelLookup->getLabel( $itemType )?->getText() ),
			'tabName' => $exists ? $tabNameMsg->parse() : null,
			'actionLink' => $this->getActionLink( $id, $exists ),
		];
	}

	private function getItemLink( string $id, ?string $label ): string {
		return $this->linkRenderer->makeLink(
			$this->getLinkTarget( "Item:$id" ),
			$label ? "$id ($label)" : $id,
			[
				'class' => 'wikibase-faceted-search-config-help__itemtypes-table-item',
			]
		);
	}

	private function getActionLink( string $id, bool $exists ): string {
		$action = $exists ? 'edit' : 'create';
		return $this->linkRenderer->makeLink(
			$this->getLinkTarget( "MediaWiki:WikibaseFacetedSearch-item-type-$id" ),
			$this->context->msg( $action )->plain(),
			[
				'class' => 'wikibase-faceted-search-config-help__itemtypes-table-action',
				'title' => $this->context->msg( "wikibase-faceted-search-config-tab-name-$action-title", $id )->plain(),
			],
			[ 'action' => 'edit' ]
		);
	}

	private function getLinkTarget( string $pageName ): LinkTarget {
		$title = $this->titleFactory->newFromText( $pageName );

		if ( $title === null ) {
			throw new RuntimeException( "Invalid page name: $pageName" );
		}

		return $title;
	}

	private function getSearchUrl(): string {
		return $this->titleFactory->newFromText( 'Special:Search' )->getFullURL();
	}

}
