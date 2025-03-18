<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch;

use CirrusSearch\CirrusSearch;
use CirrusSearch\SearchConfig;
use Elastica\Query\AbstractQuery;
use MediaWiki\Context\IContextSource;
use MediaWiki\Html\TemplateParser;
use MediaWiki\Language\Language;
use MediaWiki\Linker\LinkRendererFactory;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigAuthorizer;
use ProfessionalWiki\WikibaseFacetedSearch\Application\UserBasedConfigAuthorizer;
use ProfessionalWiki\WikibaseFacetedSearch\Application\DataValueTranslator;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ElasticQueryFilter;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemPageUpdater;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeExtractor;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\MediaWikiMessageBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PageItemLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\Application\StatementListTranslator;
use ProfessionalWiki\WikibaseFacetedSearch\Application\StatementsLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\StatementTranslator;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounter;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\CombiningConfigLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigDeserializer;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigJsonValidator;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ElasticQueryRunner;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ElasticValueCounter;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\FallbackItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\FromPageStatementsLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\NoOpItemPageUpdater;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\SitelinkItemPageUpdater;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageContentConfigLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageContentFetcher;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\PageItemLookupFactory;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\DelegatingFacetQueryBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\HasWbFacetFeature;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\ItemTypeQueryBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\ListFacetQueryBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query\RangeFacetQueryBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\SearchIndexFieldsBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\SitelinkBasedStatementsLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\ConfigDocumentationBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\DelegatingFacetHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FacetValueFormatter;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FontAwesomeIconBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\IconBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\ListFacetHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\RangeFacetHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\SidebarHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\TabsHtmlBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Wikibase\DataModel\Services\Lookup\LabelLookup;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;
use Wikibase\Lib\Store\SiteLinkStore;
use Wikibase\Repo\WikibaseRepo;

class WikibaseFacetedSearchExtension {

	public const CONFIG_PAGE_TITLE = 'WikibaseFacetedSearch';
	public const CONFIG_VARIABLE_NAME = 'WikibaseFacetedSearch';
	public const QUERY_GLOBAL = 'wgWikibaseFacetedSearchCurrentQuery';

	public const DEFAULT_CONFIG = '{
	"sitelinkSiteId": null,
	"itemTypeProperty": null,
	"configPerItemType": {}
}';

	private ?Config $config;

	public static function getInstance(): self {
		/** @var ?WikibaseFacetedSearchExtension $instance */
		static $instance = null;
		$instance ??= new self();
		return $instance;
	}

	public function getPageItemLookup(): PageItemLookup {
		return $this->newPageItemLookupFactory()->newPageItemLookup();
	}

	private function newPageItemLookupFactory(): PageItemLookupFactory {
		return new PageItemLookupFactory(
			config: $this->getConfig(),
			sitelinkLookup: $this->getSiteLinkStore()
		);
	}

	private function getSiteLinkStore(): SiteLinkStore {
		return WikibaseRepo::getStore()->newSiteLinkStore();
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
			baseConfig: (string)MediaWikiServices::getInstance()->getMainConfig()->get( self::CONFIG_VARIABLE_NAME ),
			deserializer: $this->newConfigDeserializer(),
			configLookup: $this->newPageContentConfigLookup(),
			wikiConfigIsEnabled: (bool)MediaWikiServices::getInstance()->getMainConfig()->get( 'WikibaseFacetedSearchEnableInWikiConfig' )
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

	public function getTemplateParser(): TemplateParser {
		return new TemplateParser( __DIR__ . '/../templates' );
	}

	public function getTitleFactory(): TitleFactory {
		return MediaWikiServices::getInstance()->getTitleFactory();
	}

	public function getLinkRendererFactory(): LinkRendererFactory {
		return MediaWikiServices::getInstance()->getLinkRendererFactory();
	}

	public function newSearchIndexFieldsBuilder( CirrusSearch $engine ): SearchIndexFieldsBuilder {
		return new SearchIndexFieldsBuilder(
			engine:	$engine,
			config: $this->getConfig(),
			dataTypeLookup: $this->getPropertyDataTypeLookup()
		);
	}

	private function getPropertyDataTypeLookup(): PropertyDataTypeLookup {
		return WikibaseRepo::getPropertyDataTypeLookup();
	}

	public function newStatementsLookup(): StatementsLookup {
		if ( $this->getConfig()->sitelinkSiteId === null ) {
			return new FromPageStatementsLookup();
		}

		return new SitelinkBasedStatementsLookup(
			sitelinkSiteId: $this->getConfig()->sitelinkSiteId,
			sitelinkLookup: WikibaseRepo::getStore()->newSiteLinkStore(),
			entityLookup: WikibaseRepo::getEntityLookup(),
			logger: $this->getLogger()
		);
	}

	public function newStatementListTranslator(): StatementListTranslator {
		return new StatementListTranslator(
			statementTranslator: $this->newStatementTranslator(),
			itemTypeExtractor: $this->newItemTypeExtractor(),
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

	private function newItemTypeExtractor(): ItemTypeExtractor {
		return new ItemTypeExtractor(
			itemTypeProperty: $this->getConfig()->getItemTypeProperty()
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

	public function getSidebarHtmlBuilder( Language $language, AbstractQuery $currentQuery ): SidebarHtmlBuilder {
		return new SidebarHtmlBuilder(
			config: $this->getConfig(),
			facetHtmlBuilder: $this->getFacetHtmlBuilder( $language, $currentQuery ),
			labelLookup: $this->getLabelLookup( $language ),
			templateParser: $this->getTemplateParser(),
			queryStringParser: $this->getQueryStringParser()
		);
	}

	private function getFacetHtmlBuilder( Language $language, AbstractQuery $currentQuery ): FacetHtmlBuilder {
		$delegator = new DelegatingFacetHtmlBuilder();
		$delegator->addBuilder( FacetType::LIST, $this->newListFacetHtmlBuilder(
			$this->getFacetValueFormatter( $language ), $currentQuery
		) );
		$delegator->addBuilder( FacetType::RANGE, $this->newRangeFacetHtmlBuilder() );
		return $delegator;
	}

	public function getFacetValueFormatter( Language $language ): FacetValueFormatter {
		return new FacetValueFormatter(
			dataTypeLookup: $this->getPropertyDataTypeLookup(),
			labelLookup: $this->getLabelLookup( $language )
		);
	}

	private function newListFacetHtmlBuilder(
		FacetValueFormatter $valueFormatter,
		AbstractQuery $currentQuery
	): FacetHtmlBuilder {
		return new ListFacetHtmlBuilder(
			parser: $this->getTemplateParser(),
			valueCounter: $this->getValueCounter( $currentQuery ),
			valueFormatter: $valueFormatter
		);
	}

	public function getValueCounter( AbstractQuery $currentQuery ): ValueCounter {
		return new ElasticValueCounter(
			queryRunner: $this->getElasticQueryRunner(),
			currentQuery: $currentQuery
		);
	}

	public function getElasticQueryRunner(): ElasticQueryRunner {
		return new ElasticQueryRunner(
			$this->getSearchConfig()
		);
	}

	private function getSearchConfig(): SearchConfig {
		/**
		 * @var SearchConfig $config
		 */
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'CirrusSearch' );
		return $config;
	}

	private function newRangeFacetHtmlBuilder(): FacetHtmlBuilder {
		return new RangeFacetHtmlBuilder(
			parser: $this->getTemplateParser()
		);
	}

	public function getQueryStringParser(): QueryStringParser {
		return new QueryStringParser(
			itemTypeProperty: $this->getConfig()->getItemTypeProperty()
		);
	}

	private function getItemTypeLabelLookup( Language $language ): ItemTypeLabelLookup {
		return new FallbackItemTypeLabelLookup(
			labelLookup: $this->getLabelLookup( $language ),
			messageBuilder: new MediaWikiMessageBuilder()
		);
	}

	public function getLabelLookup( Language $language ): LabelLookup {
		return WikibaseRepo::getFallbackLabelDescriptionLookupFactory()->newLabelDescriptionLookup( $language );
	}

	public function newHasWbFacetFeature(): HasWbFacetFeature {
		return new HasWbFacetFeature(
			config: $this->getConfig(),
			queryStringParser: $this->getQueryStringParser(),
			itemTypeQueryBuilder: $this->getItemTypeQueryBuilder(),
			facetQueryBuilder: $this->getFacetQueryBuilder()
		);
	}

	private function getItemTypeQueryBuilder(): ItemTypeQueryBuilder {
		return new ItemTypeQueryBuilder(
			itemTypeProperty: $this->getConfig()->getItemTypeProperty()
		);
	}

	private function getFacetQueryBuilder(): DelegatingFacetQueryBuilder {
		$delegator = new DelegatingFacetQueryBuilder();
		$delegator->addBuilder( FacetType::LIST, $this->newListFacetQueryBuilder() );
		$delegator->addBuilder( FacetType::RANGE, $this->newRangeFacetQueryBuilder() );
		return $delegator;
	}

	private function newListFacetQueryBuilder(): ListFacetQueryBuilder {
		return new ListFacetQueryBuilder(
			dataTypeLookup: $this->getPropertyDataTypeLookup()
		);
	}

	private function newRangeFacetQueryBuilder(): RangeFacetQueryBuilder {
		return new RangeFacetQueryBuilder(
			dataTypeLookup: $this->getPropertyDataTypeLookup()
		);
	}

	public function getTabsHtmlBuilder( Language $language, User $user ): TabsHtmlBuilder {
		return new TabsHtmlBuilder(
			config: $this->getConfig(),
			itemTypeLabelLookup: $this->getItemTypeLabelLookup( $language ),
			templateParser: $this->getTemplateParser(),
			queryStringParser: $this->getQueryStringParser(),
			configAuthorizer: $this->newConfigAuthorizer( $user ),
			titleFactory: $this->getTitleFactory(),
			iconBuilder: $this->newIconBuilder(),
		);
	}

	public function newConfigAuthorizer( User $user ): ConfigAuthorizer {
		return new UserBasedConfigAuthorizer(
			wikiConfigIsEnabled: (bool)MediaWikiServices::getInstance()->getMainConfig()->get( 'WikibaseFacetedSearchEnableInWikiConfig' ),
			user: $user
		);
	}

	public function newIconBuilder(): IconBuilder {
		return new FontAwesomeIconBuilder();
	}

	public function newElasticQueryFilter(): ElasticQueryFilter {
		return new ElasticQueryFilter();
	}

	public function newItemPageUpdater(): ItemPageUpdater {
		if ( $this->getConfig()->sitelinkSiteId === null ) {
			return new NoOpItemPageUpdater();
		}

		return new SitelinkItemPageUpdater(
			sitelinkSiteId: $this->getConfig()->sitelinkSiteId,
			pageFactory: MediaWikiServices::getInstance()->getWikiPageFactory(),
			titleFactory: $this->getTitleFactory()
		);
	}

	public function newConfigDocumentationBuilder( IContextSource $context ): ConfigDocumentationBuilder {
		return new ConfigDocumentationBuilder(
			context: $context,
			exampleConfigPath: $this->getExampleConfigPath(),
			templateParser: $this->getTemplateParser(),
			config: $this->getConfig(),
			titleFactory: $this->getTitleFactory(),
			linkRenderer: $this->getLinkRendererFactory()->create(),
			labelLookup: $this->getLabelLookup( $context->getLanguage() )
		);
	}

	private function getLogger(): LoggerInterface {
		return LoggerFactory::getInstance( 'WikibaseFacetedSearch' );
	}

}
