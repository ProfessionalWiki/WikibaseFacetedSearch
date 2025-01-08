<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use MediaWiki\Utils\UrlUtils;

class SearchUrlBuilder {

	/** @var array<string, string> */
	private array $urlParts = [];

	/** @var array<string, string> */
	private array $urlQuery = [];

	public function __construct(
		private readonly UrlUtils $urlUtils
	) {
	}

	public function setUrlParts( string $url ): void {
		$this->urlParts = $this->urlUtils->parse( $url ) ?? [];
	}

	/**
	 * @return array<string, string>
	 */
	public function getUrlParts(): array {
		return $this->urlParts;
	}

	public function setUrlQuery(): void {
		$this->urlQuery = wfCgiToArray( (string)$this->urlParts['query'] );
	}

	/**
	 * @return array<string, string>
	 */
	public function getUrlQuery(): array {
		return $this->urlQuery;
	}

	// TODO: Use PropertyId for $propertyId
	public function buildUrl( string $facetQuery = '' ): string {
		if ( $facetQuery === '' ) {
			return $this->urlUtils->assemble( $this->urlParts );
		}

		$urlQuery = $this->urlQuery;
		if ( str_contains( $urlQuery['search'], $facetQuery ) ) {
			$urlQuery['search'] = str_replace( $facetQuery, '', $urlQuery['search'] );
			$urlQuery['search'] = trim( $urlQuery['search'] );
		} else {
			$urlQuery['search'] = $urlQuery['search'] . ' ' . $facetQuery;
		}

		$urlParts = $this->urlParts;
		$urlParts['query'] = wfArrayToCgi( $urlQuery );
		return $this->urlUtils->assemble( $urlParts );
	}

}
