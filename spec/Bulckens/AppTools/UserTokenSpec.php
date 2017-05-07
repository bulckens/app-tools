<?php

namespace spec\Bulckens\AppTools;

use Bulckens\Helpers\StringHelper;
use Bulckens\AppTools\App;
use Bulckens\AppTools\UserToken;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserTokenSpec extends ObjectBehavior {

  function let() {
    // initialize app
    $app = new App( 'dev' );
    $app->run();
  }
  

  // Initialization
  function it_fails_when_no_secret_could_be_found() {
    $this
      ->shouldThrow( 'Bulckens\AppTools\TokenSecretMissingException' )
      ->during__construct( 'gGOml0Nt9PiCr09lYWt5z123kIu1y4Hk', 'phish' );
  }


  // Get method
  function it_returns_the_generated_token() {
    $code = StringHelper::generate( 32 );
    $this->beConstructedWith( $code, 'generic' );
    $this->get()->shouldMatch( '/^[a-z0-9]{64}[A-Za-z0-9]{32}$/' );
  }

  function it_generates_a_valid_token() {
    $code   = StringHelper::generate( 32 );
    $secret = '1234567891011121314151617181920212223242526272829303132333435363';
    $token  = hash( 'sha256', implode( '---', [ $secret, $code ] ) ) . strrev( $code );

    $this->beConstructedWith( $code, 'generic' );
    $this->get()->shouldEndWith( $token );
    $this->get()->shouldBe( $token );
  }


  // Validate method
  function it_verifies_the_validity_of_a_token() {
    $code  = StringHelper::generate( 32 );
    $token = new UserToken( $code, 'generic' );
    $this->beConstructedWith( $code, 'generic' );
    $this->validate( $token->get() )->shouldBe( true );
  }

  function it_verifies_the_invalidity_of_a_token() {
    $code  = StringHelper::generate( 32 );
    $token = new UserToken( $code, 'reverse' );
    $this->beConstructedWith( $code, 'generic' );
    $this->validate( $token->get() )->shouldBe( false );
  }


  // Login method
  function it_returns_the_given_code() {
    $this->beConstructedWith( 'gGOml0Nt9PiCr09lYWt5z123kIu1y4Hk', 'generic' );
    $this->code()->shouldBe( 'gGOml0Nt9PiCr09lYWt5z123kIu1y4Hk' );
  }


  // Secret method
  function it_returns_the_secret_for_a_given_key() {
    $this->beConstructedWith( 'gGOml0Nt9PiCr09lYWt5z123kIu1y4Hk', 'generic' );
    $this->secret()->shouldBe( '1234567891011121314151617181920212223242526272829303132333435363' );
  }


  // PersistenceCode static method
  function it_parses_a_given_token_into_a_persistencecode() {
    $code  = StringHelper::generate( 32 );
    $token = new UserToken( $code, 'generic' );
    $this->beConstructedWith( $code, 'generic' );
    $this::persistenceCode( $token->get() )->shouldBe( $code );
  }


  // Match static method
  function it_matches_a_given_value_as_a_user_token() {
    $code  = StringHelper::generate( 32 );
    $this->beConstructedWith( $code, 'generic' );
    $this::match( $this->get() )->shouldBeArray();
  }

  function it_returns_the_matches_for_a_given_user_token() {
    $code  = StringHelper::generate( 32 );
    $this->beConstructedWith( $code, 'generic' );
    $match = $this::match( $this->get() );
    $match[1]->shouldMatch( '/^[a-z0-9]{64}$/' );
    $match[2]->shouldBe( strrev( $code ) );
  }

  function it_fails_to_match_a_given_value_as_a_user_token() {
    $code  = StringHelper::generate( 32 );
    $this->beConstructedWith( $code, 'generic' );
    $this::match( $code )->shouldBe( null );
  }


  // ToString magic method
  function it_is_magically_convertable_to_a_string() {
    $code = StringHelper::generate( 32 );
    $token = new UserToken( $code, 'generic' );
    $this->beConstructedWith( $code, 'generic' );
    $this->get()->shouldBe( "$token" );
  }


}
