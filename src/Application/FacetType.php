<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

enum FacetType: string {
	case LIST = 'list';
	case RANGE = 'range';
}
