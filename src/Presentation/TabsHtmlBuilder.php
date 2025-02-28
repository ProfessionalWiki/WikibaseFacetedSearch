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
		private readonly ItemTypeLabelLookup $itemTypeLabelLookup,
		private readonly TemplateParser $templateParser,
		private readonly QueryStringParser $queryStringParser,
		private readonly ConfigAuthorizer $configAuthorizer,
		private readonly TitleFactory $titleFactory,
		private readonly IconBuilder $iconBuilder,
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

		if ( !$this->configAuthorizer->isAuthorized( $title ) ) {
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
			$tabs[] = $this->buildTabViewModel( $itemType, $selectedItemType );
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

	private function buildTabViewModel( ItemId $itemType, ?ItemId $selectedItemType ): array {
		$tab = [	
			'label' => $this->itemTypeLabelLookup->getLabel( $itemType ),
			'value' => $itemType->getSerialization(),
			'selected' => $itemType->equals( $selectedItemType ),
		];

		$icon = $this->buildTabIcon( $itemType );
		if ( $icon !== null ) {
			$tab['icon'] = $icon;
		}

		return $tab;
	}

	private function buildTabIcon( ItemId $itemType ): ?string {
		$icon = $this->config->getIconForItemType( $itemType );

		if ( $icon === null ) {
			return null;
		}

		return $this->iconBuilder->buildHtml( $icon );
	}

	private function noTabsAreSelected( array $tabs ): bool {
		return !array_reduce( $tabs, ( fn( $carry, $tab ) => $carry || $tab['selected'] ), false );
	}

}
