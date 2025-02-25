<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Html\TemplateParser;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigAuthorizer;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\ItemId;

class TabsHtmlBuilder {

	public function __construct(
		private readonly Config $config,
		private readonly ConfigAuthorizer $configAuthorizer,
		private readonly ItemTypeLabelLookup $itemTypeLabelLookup,
		private readonly TemplateParser $templateParser,
		private readonly TitleFactory $titleFactory,
		private readonly QueryStringParser $queryStringParser
	) {
	}

	public function createHtml( string $searchQuery ): string {
		$query = $this->parseQuery( $searchQuery );
		$itemType = $query->getItemTypes()[0] ?? null;

		return $this->renderTemplate(
			$this->buildTabsViewModel(
				selectedItemType: $itemType
			)
		);
	}

	private function renderTemplate( array $instancesViewModel ): string {
		return $this->templateParser->processTemplate(
			'Topbar',
			[
				'instanceId' => $this->config->getItemTypeProperty()->getSerialization(),
				'instances' => $instancesViewModel,
				'settings' => $this->buildSettingsViewModel()
			]
		);
	}

	private function parseQuery( string $searchQuery ): Query {
		return $this->queryStringParser->parse( $searchQuery );
	}

	private function buildSettingsViewModel(): array {
		$title = $this->titleFactory->newFromText( WikibaseFacetedSearchExtension::CONFIG_PAGE_TITLE, NS_MEDIAWIKI );

		if ( !$title instanceof Title ) {
			return [];
		}

		if ( !$this->configAuthorizer->isAuthorized( $title->toPageIdentity() ) ) {
			return [];
		}

		return [
			'url' => $title->getFullURL(),
			'label' => wfMessage( 'wikibase-faceted-search-settings' )->text(),
		];
	}

	private function buildTabsViewModel( ?ItemId $selectedItemType ): array {
		$tabs = [];

		foreach ( $this->config->getItemTypes() as $itemType ) {
			$tabs[] = [
				'label' => $this->itemTypeLabelLookup->getLabel( $itemType ),
				'value' => $itemType->getSerialization(),
				'selected' => $itemType->equals( $selectedItemType )
			];
		}

		return [
			[
				'label' => wfMessage( 'wikibase-faceted-search-instance-type-all' )->text(),
				'value' => '',
				'selected' => $this->noTabsAreSelected( $tabs )
			],
			...$tabs
		];
	}

	/**
	 * @param array<array{selected: bool}> $tabs
	 */
	private function noTabsAreSelected( array $tabs ): bool {
		return !array_reduce( $tabs, ( fn( $carry, $tab ) => $carry || $tab['selected'] ), false );
	}

}
