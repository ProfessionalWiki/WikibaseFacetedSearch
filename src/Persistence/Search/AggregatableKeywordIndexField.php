<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search;

use CirrusSearch\Search\CirrusIndexField;

class AggregatableKeywordIndexField extends CirrusIndexField {

	protected $typeName = 'keyword';

}
