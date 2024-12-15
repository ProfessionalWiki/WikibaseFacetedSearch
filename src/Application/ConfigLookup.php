<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

interface ConfigLookup {

	public function getConfig(): Config;

}
