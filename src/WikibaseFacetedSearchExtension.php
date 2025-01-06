<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\DataValueTranslator;
use ProfessionalWiki\WikibaseFacetedSearch\Application\InstanceTypeExtractor;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemPageLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\StatementListTranslator;
use ProfessionalWiki\WikibaseFacetedSearch\Application\StatementsLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\StatementTranslator;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\CombiningConfigLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigDeserializer;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigJsonValidator;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\FromPageStatementsLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ItemPageLookupFactory;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageContentConfigLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageContentFetcher;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\SearchIndexFieldsBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\SitelinkBasedStatementsLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetUiBuilder;
use RuntimeException;
use SearchEngine;
use TemplateParser;
use Title;
use Wikibase\Repo\WikibaseRepo;

class WikibaseFacetedSearchExtension {

	public const CONFIG_PAGE_TITLE = 'WikibaseFacetedSearch';

	public const DEFAULT_CONFIG = '{
	"linkTargetSitelinkSiteId": null,
	"instanceOfId": null,
	"facets": {}
}';

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
			validator: $this->newConfigJsonValidator()
		);
	}

	public function newFacetUiBuilder(): FacetUiBuilder {
		return new FacetUiBuilder(
			parser: new TemplateParser( __DIR__ . '/../templates' ),
			config: $this->getConfig()
		);
	}

	public function newSearchIndexFieldsBuilder( SearchEngine $engine ): SearchIndexFieldsBuilder {
		return new SearchIndexFieldsBuilder(
			engine:	$engine,
			config: $this->getConfig(),
			dataTypeLookup: WikibaseRepo::getPropertyDataTypeLookup()
		);
	}

	public function newStatementsLookup(): StatementsLookup {
		if ( $this->getConfig()->linkTargetSitelinkSiteId === null ) {
			return new FromPageStatementsLookup();
		}

		return new SitelinkBasedStatementsLookup(
			linkTargetSitelinkSiteId: $this->getConfig()->linkTargetSitelinkSiteId,
			sitelinkLookup: WikibaseRepo::getStore()->newSiteLinkStore(),
			entityLookup: WikibaseRepo::getEntityLookup()
		);
	}

	public function newStatementListTranslator(): StatementListTranslator {
		return new StatementListTranslator(
			statementTranslator: $this->newStatementTranslator(),
			instanceTypeExtractor: $this->newInstanceTypeExtractor(),
			config: $this->getConfig()
		);
	}

	private function newStatementTranslator(): StatementTranslator {
		return new StatementTranslator(
			dataValueTranslator: $this->newDataValueTranslator()
		);
	}

	private function newDataValueTranslator(): DataValueTranslator {
		return new DataValueTranslator();
	}

	private function newInstanceTypeExtractor(): InstanceTypeExtractor {
		return new InstanceTypeExtractor(
			instanceType: $this->getConfig()->getInstanceOfId()
		);
	}

	public function getExampleConfigPath(): string {
		return __DIR__ . '/config-example.json';
	}

	public function newConfigJsonValidator(): ConfigJsonValidator {
		$json = file_get_contents( __DIR__ . '/config-schema.json' );

		if ( !is_string( $json ) ) {
			throw new RuntimeException( 'Could not obtain JSON Schema' );
		}

		$schema = json_decode( $json );

		if ( !is_object( $schema ) ) {
			throw new RuntimeException( 'Failed to deserialize JSON Schema' );
		}

		return new ConfigJsonValidator( $schema );
	}

}
