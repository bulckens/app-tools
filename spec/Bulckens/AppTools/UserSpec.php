<?php

namespace spec\Bulckens\AppTools;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Cartalyst\Sentinel\Users\EloquentUser;
use Cartalyst\Sentinel\Reminders\EloquentReminder;
use Cartalyst\Sentinel\Activations\EloquentActivation;
use Cartalyst\Sentinel\Persistences\EloquentPersistence;
use Bulckens\AppTools\App;
use Bulckens\AppTools\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
  }

  function letGo() {
    EloquentUser::truncate();
    EloquentReminder::truncate();
    EloquentActivation::truncate();
    EloquentPersistence::truncate();
    Sentinel::logout();
  }


  // Initialization
  function it_initializes_with_the_default_configuration() {
    $this->shouldHaveType( User::class );
  }

  function it_initializes_with_the_bare_configuration() {
    $this->file( 'user_bare.yml' );
    $this->shouldHaveType( User::class );
  }

  
  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    $this->config( 'checkpoints' )->shouldBeArray();
  }

  function it_returns_a_given_default_value_if_key_is_not_existing() {
    $this->config( 'pater', 'nostrum' )->shouldBe( 'nostrum' );
  }


  // File method
  function it_builds_config_file_name_from_class() {
    $this->file()->shouldBe( 'user.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->file( 'user_custom.yml' );
    $this->file()->shouldBe( 'user_custom.yml' );
    $this->config( 'custom' )->shouldBe( 'custom' );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->file( 'user_custom.yml' );
    $this->file()->shouldBe( 'user_custom.yml' );
    $this->file( null );
    $this->file()->shouldBe( 'user.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->file( 'user_custom.yml' )->shouldBe( $this );
  }


  // Register method
  function it_registeres_a_new_user() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ]);
    $this::find( 'we@you.them' )
      ->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }

  function it_registeres_and_activates_a_new_user() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ], true );
    $this::login( 'we@you.them', '12345678' )
      ->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }

  function it_returns_a_user_after_registering() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ])
      ->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }

  function it_registeres_a_new_user_without_activating() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ]);
    $this::shouldThrow( 'Cartalyst\Sentinel\Checkpoints\NotActivatedException' )
      ->duringLogin( 'we@you.them', '12345678' );
  }

  function it_registeres_a_new_user_without_explicitly_activating() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ], false );
    $this::shouldThrow( 'Cartalyst\Sentinel\Checkpoints\NotActivatedException' )
      ->duringLogin( 'we@you.them', '12345678' );
  }


  // Login method
  function it_authenticates_a_user() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ], true );
    $this::login( 'we@you.them', '12345678' )
      ->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }

  function it_authenticates_and_remembers_a_user() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ], true );
    $this::login( 'we@you.them', '12345678', true )->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }

  function it_fails_to_authenticate_a_user_with_wrong_credentials() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ], true );
    $this::login( 'them@you.me', '12345678', true )->shouldBe( false );
  }


  // Logout method
  function it_logs_the_current_user_out() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ], true );
    $this::login( 'we@you.them', '12345678' );
    $this::loggedIn()->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
    $this::logout();
    $this::loggedIn()->shouldBe( false ); 
  }

  function it_returns_null_after_logging_out() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ], true );
    $this::login( 'we@you.them', '12345678' );
    $this::logout()->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }


  // LoggedIn method
  function it_is_positive_if_a_user_is_logged_in() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ], true );
    $this::login( 'we@you.them', '12345678' );
    $this::loggedIn()->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }

  function it_is_negative_if_a_user_is_not_logged_in() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ], true );
    $this::loggedIn()->shouldBe( false ); 
  }


  // Get method
  function it_returns_the_currently_logged_in_user() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ], true );
    $this::login( 'we@you.them', '12345678' );
    $this::get()->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }

  function it_returns_null_if_no_user_is_logged_in() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ], true );
    $this::get()->shouldBe( null );
  }


  // Find method
  function it_finds_a_user_by_id() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ]);
    $this::find( 1 )->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }

  function it_finds_a_user_by_email() {
    $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ]);
    $this::find( 'we@you.them' )
      ->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }

  function it_accepts_a_user_object_and_returns_it() {
    $user = $this::register([ 'email' => 'we@you.them', 'password' => '12345678' ]);
    $this::find( $user )->shouldBe( $user );
  }

  function it_fails_when_a_user_could_not_be_found() {
    $this::shouldThrow( 'Bulckens\AppTools\UserNotFoundException' )
      ->duringFind( 'no@no.no' );
  }


  // ResetCode method
  function it_generates_a_password_reset_code_for_a_given_email_address() {
    $this::register([ 'email' => 'yes@yes.yes', 'password' => '12345678' ], true );
    $this::resetCode( 'yes@yes.yes' )->shouldMatch( '/^[a-zA-Z0-9]{32}$/' );
  }

  function it_generates_a_password_reset_code_for_a_given_user() {
    $user = $this::register([ 'email' => 'yes@yes.yes', 'password' => '12345678' ], true );
    $this::resetCode( $user )->shouldMatch( '/^[a-zA-Z0-9]{32}$/' );
  }

  function it_fails_to_generate_a_reset_code_when_the_user_does_not_exist() {
    $this::shouldThrow( 'Bulckens\AppTools\UserNotFoundException' )
      ->duringResetCode( 'no@no.no' );
  }


  // ResetPassword method
  function it_resets_the_password_for_a_given_email_address() {
    $this::register([ 'email' => 'yes@yes.yes', 'password' => '12345678' ], true );
    $object = $this::getWrappedObject();
    $code = $object::resetCode( 'yes@yes.yes' );
    $this::resetPassword( 'yes@yes.yes', '87654321', $code );
    $this::login( 'yes@yes.yes', '87654321', true )
      ->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }

  function it_resets_the_password_for_a_given_user() {
    $user = $this::register([ 'email' => 'yes@yes.yes', 'password' => '12345678' ], true );
    $code = $this::resetCode( 'yes@yes.yes' );
    $this::resetPassword( $user, '87654321', $code );
    $this::login( 'yes@yes.yes', '87654321', true )
      ->shouldHaveType( 'Cartalyst\Sentinel\Users\EloquentUser' );
  }

  function it_fails_to_reset_the_password_when_the_user_does_not_exist() {
    $this::shouldThrow( 'Bulckens\AppTools\UserNotFoundException' )
      ->duringResetCode( 'no@no.no' );
  }

  function it_fails_to_reset_the_password_with_a_wrong_code() {
    $this::register([ 'email' => 'yes@yes.yes', 'password' => '12345678' ], true );
    $this::shouldThrow( 'Bulckens\AppTools\UserResetCodeNotValidException' )
      ->duringResetPassword( 'yes@yes.yes', '87654321', 'hihihisisisja' );
  }


}
