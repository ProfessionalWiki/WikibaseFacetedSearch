<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use MediaWiki\Page\PageIdentityValue;
use MediaWiki\User\User;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigAuthorizer;
use ProfessionalWiki\WikibaseFacetedSearch\Application\UserBasedConfigAuthorizer;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubUser;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\UserBasedConfigAuthorizer
 */
class UserBasedConfigAuthorizerTest extends TestCase {

	private function newConfigAuthorizer( ?bool $wikiConfigIsEnabled = false, ?User $user = null ): ConfigAuthorizer {
		return new UserBasedConfigAuthorizer(
			wikiConfigIsEnabled: $wikiConfigIsEnabled,
			user: $user ?? $this->newUser()
		);
	}

	private function newUser( bool $canEdit = false ): User {
		return new StubUser( probablyCan: $canEdit );
	}

	private function newConfigPageIdentity(): PageIdentityValue {
		return new PageIdentityValue( 1, NS_MEDIAWIKI, 'WikibaseFacetedSearch', PageIdentityValue::LOCAL );
	}

	public function testNotAuthorizedWhenWikiConfigDisabled(): void {
		$authorizer = $this->newConfigAuthorizer(
			wikiConfigIsEnabled: false
		);

		$this->assertFalse( $authorizer->isAuthorized( $this->newConfigPageIdentity() ) );
	}
	
	public function testAuthorizedWhenUserCanEdit(): void {		
		$page = $this->newConfigPageIdentity();
		$authorizer = $this->newConfigAuthorizer(
			wikiConfigIsEnabled: true,
			user: $this->newUser( canEdit: true )
		);

		$this->assertTrue( $authorizer->isAuthorized( $page ) );
	}
	
	public function testNotAuthorizedWhenUserCannotEdit(): void {
		$page = $this->newConfigPageIdentity();
		$authorizer = $this->newConfigAuthorizer(
			wikiConfigIsEnabled: true,
			user: $this->newUser( canEdit: false )
		);

		$this->assertFalse( $authorizer->isAuthorized( $page ) );
	}

}
