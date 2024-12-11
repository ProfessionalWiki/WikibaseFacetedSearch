<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

enum FacetType: string {
	case BOOLEAN = 'boolean';
	case LIST = 'list';
	case RANGE = 'range';
}
