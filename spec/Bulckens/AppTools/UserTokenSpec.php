<?php

namespace spec\Bulckens\AppTools;

use Bulckens\Helpers\StringHelper;
use Bulckens\Helpers\TimeHelper;
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
      ->shouldThrow( 'Bulckens\AppTools\Traits\TokenishSecretMissingException' )
      ->during__construct( 'gGOml0Nt9PiCr09lYWt5z123kIu1y4Hk', 'phish' );
  }


  // Get method
  function it_returns_the_generated_token() {
    $code = StringHelper::generate( 32 );
    $this->beConstructedWith( $code, 'generic' );
    $this->get()->shouldMatch( '/^[a-z0-9]{64}[a-z0-9]{11}[A-Za-z0-9]{32}$/' );
  }

  function it_generates_a_valid_token() {
    $code   = StringHelper::generate( 32 );
    $stamp  = TimeHelper::ms();
    $secret = '1234567891011121314151617181920212223242526272829303132333435363';
    $token  = hash( 'sha256', implode( '---', [ $secret, $stamp, $code ] ) ) . dechex( $stamp ) . strrev( $code );

    $this->beConstructedWith( $code, 'generic', $stamp );
    $this->get()->shouldEndWith( $token );
    $this->get()->shouldBe( $token );
  }


  // Hash method
  function it_hashes_the_given_parts() {
    $code  = StringHelper::generate( 32 );
    $stamp = TimeHelper::ms();
    $parts = [ 'a', 'b', 'c' ];
    $hash  = hash( 'sha256', implode( '---', $parts ) ) . dechex( $stamp );

    $this->beConstructedWith( $code, 'generic', $stamp );
    $this->hash( $parts )->shouldBe( $hash );
  }


  // Validate method
  function it_verifies_the_validity_of_a_token() {
    $code  = StringHelper::generate( 32 );
    $stamp = TimeHelper::ms();
    $token = new UserToken( $code, 'generic', $stamp );
    $this->beConstructedWith( $code, 'generic', $stamp );
    $this->validate( $token->get() )->shouldBe( true );
  }

  function it_verifies_the_validity_of_a_token_using_its_timestamp() {
    $code  = StringHelper::generate( 32 );
    $stamp = TimeHelper::ms();
    $token = new UserToken( $code, 'generic', $stamp );
    $this->beConstructedWith( $code, 'generic' );
    $this->validate( $token->get() )->shouldBe( true );
  }

  function it_verifies_the_validity_of_a_token_using_its_timestamp_only_if_no_stamp_was_given() {
    $code  = StringHelper::generate( 32 );
    $stamp = TimeHelper::ms();
    $token = new UserToken( $code, 'generic', $stamp );
    $this->beConstructedWith( $code, 'generic', $stamp + 100 );
    $this->validate( $token->get() )->shouldBe( false );
  }

  function it_verifies_the_invalidity_of_a_token() {
    $code  = StringHelper::generate( 32 );
    $stamp = TimeHelper::ms();
    $token = new UserToken( $code, 'reverse', $stamp );
    $this->beConstructedWith( $code, 'generic', $stamp );
    $this->validate( $token->get() )->shouldBe( false );
  }


  // Verify method
  function it_verifies_the_existence_of_a_secret() {
    $this->beConstructedWith( StringHelper::generate( 32 ), 'generic', TimeHelper::ms() );
    $this
      ->shouldNotThrow( 'Bulckens\AppTools\Traits\TokenishSecretMissingException' )
      ->duringVerify( 'generic' );
  }

  function it_returns_itself_after_verifying_the_existance_of_a_token() {
    $this->beConstructedWith( StringHelper::generate( 32 ), 'generic', TimeHelper::ms() );
    $this->verify( 'generic' )->shouldBe( $this );
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


  // Stamp method
  function it_returns_the_given_timestamp() {
    $stamp = TimeHelper::ms();
    $this->beConstructedWith( 'gGOml0Nt9PiCr09lYWt5z123kIu1y4Hk', 'generic', $stamp );
    $this->stamp()->shouldBe( $stamp );
  }

  function it_returns_the_generated_timestamp() {
    $this->beConstructedWith( 'gGOml0Nt9PiCr09lYWt5z123kIu1y4Hk', 'generic' );
    $this->stamp()->shouldBeDouble();
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
    $match[2]->shouldMatch( '/^[a-z0-9]{11}$/' );
    $match[3]->shouldBe( strrev( $code ) );
  }

  function it_fails_to_match_a_given_value_as_a_user_token() {
    $code  = StringHelper::generate( 32 );
    $this->beConstructedWith( $code, 'generic' );
    $this::match( $code )->shouldBe( null );
  }


  // ToString magic method
  function it_is_magically_convertable_to_a_string() {
    $code  = StringHelper::generate( 32 );
    $stamp = TimeHelper::ms();
    $token = new UserToken( $code, 'generic', $stamp );
    $this->beConstructedWith( $code, 'generic', $stamp );
    $this->get()->shouldBe( "$token" );
  }


  // Timestamp static method
  function it_parses_a_given_token_into_token_and_timestamp() {
    $code = StringHelper::generate( 32 );
    $stamp = intval( TimeHelper::ms() );
    $token = new UserToken( $code, 'generic', $stamp );
    $this->beConstructedWith( $code, 'generic', $stamp );
    $this::timestamp( $token->get() )->shouldBe( $stamp );
  }


}
