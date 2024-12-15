<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\CombiningConfigLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigDeserializer;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigJsonValidator;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemPageLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ItemPageLookupFactory;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageContentConfigLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageContentFetcher;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetUiBuilder;
use TemplateParser;
use Title;

class WikibaseFacetedSearchExtension {

	public const CONFIG_PAGE_TITLE = 'WikibaseFacetedSearch';

	private ?Config $config;

	public static function getInstance(): self {
		/** @var ?WikibaseFacetedSearchExtension $instance */
		static $instance = null;
		$instance ??= new self();
		return $instance;
	}

	public function getItemPageLookup(): ItemPageLookup {
		return $this->newItemPageLookupFactory()->newItemPageLookup();
	}

	private function newItemPageLookupFactory(): ItemPageLookupFactory {
		return new ItemPageLookupFactory(
			$this->getConfig()
		);
	}

	public function isConfigTitle( Title $title ): bool {
		return $title->getNamespace() === NS_MEDIAWIKI && $title->getText() === self::CONFIG_PAGE_TITLE;
	}

	public function getConfig(): Config {
		$this->config ??= $this->newConfigLookup()->getConfig();
		return $this->config;
	}

	public function clearConfig(): void {
		$this->config = null;
	}

	private function newConfigLookup(): ConfigLookup {
		return new CombiningConfigLookup(
			baseConfig: (string)MediaWikiServices::getInstance()->getMainConfig()->get( 'WikibaseFacetedSearch' ),
			deserializer: $this->newConfigDeserializer(),
			configLookup: $this->newPageContentConfigLookup(),
			enableWikiConfig: (bool)MediaWikiServices::getInstance()->getMainConfig()->get( 'WikibaseFacetedSearchEnableInWikiConfig' )
		);
	}

	public function newPageContentConfigLookup(): PageContentConfigLookup {
		return new PageContentConfigLookup(
			contentFetcher: new PageContentFetcher(
				MediaWikiServices::getInstance()->getTitleParser(),
				MediaWikiServices::getInstance()->getRevisionLookup()
			),
			deserializer: $this->newConfigDeserializer(),
			pageName: self::CONFIG_PAGE_TITLE
		);
	}

	public function newConfigDeserializer(): ConfigDeserializer {
		return new ConfigDeserializer(
			ConfigJsonValidator::newInstance()
		);
	}

	public function newFacetUiBuilder(): FacetUiBuilder {
		return new FacetUiBuilder(
			parser: new TemplateParser( __DIR__ . '/../templates' ),
			config: $this->getConfig()
		);
	}

}
