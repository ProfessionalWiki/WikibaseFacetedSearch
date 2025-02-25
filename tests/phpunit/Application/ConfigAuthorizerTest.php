<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\User\User;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigAuthorizer;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigAuthorizer
 */
class ConfigAuthorizerTest extends TestCase {

	private function newConfigAuthorizer( ?bool $enableWikiConfig = false, ?User $user = null ): ConfigAuthorizer {
		return new ConfigAuthorizer(
			enableWikiConfig: $enableWikiConfig,
			user: $user ?? $this->newUser()
		);
	}

	private function newUser( bool $canEditConfig = false ): User {
		$user = $this->createMock( User::class );
		$user->method( 'probablyCan' )
			->willReturn( $canEditConfig );
		return $user;
	}

	public function testNotAuthorizedWhenWikiConfigDisabled(): void {
		$authorizer = $this->newConfigAuthorizer(
			enableWikiConfig: false
		);

		$page = $this->createMock( ProperPageIdentity::class );

		$this->assertFalse( $authorizer->isAuthorized( $page ) );
	}
	
	public function testAuthorizedWhenUserCanEdit(): void {		
		$authorizer = $this->newConfigAuthorizer(
			enableWikiConfig: true,
			user: $this->newUser( canEditConfig: true )
		);

		$page = $this->createMock( ProperPageIdentity::class );

		$this->assertTrue( $authorizer->isAuthorized( $page ) );
	}
	
	public function testNotAuthorizedWhenUserCannotEdit(): void {
		$authorizer = $this->newConfigAuthorizer(
			enableWikiConfig: true,
			user: $this->newUser( canEditConfig: false )
		);

		$page = $this->createMock( ProperPageIdentity::class );

		$this->assertFalse( $authorizer->isAuthorized( $page ) );
	}

}
