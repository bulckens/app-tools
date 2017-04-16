<?php

namespace Bulckens\AppTools;

use Exception;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Bulckens\AppTools\Traits\Configurable;

class User {

  use Configurable;

  protected static $activate_on_signup = true;

  // Initialize database connection
  public function __construct() {
    if ( $checkpoints = $this->config( 'checkpoints' ) ) {
      // setup checkpoints
      Sentinel::enableCheckpoints();

      // configure activation checkpoint
      if ( in_array( 'activation', $checkpoints ) )
        self::$activate_on_signup = $this->config( 'signup.activate', false );
      else
        Sentinel::removeCheckpoint( 'activation' );

      // configure activation checkpoint
      if ( ! in_array( 'throttle', $checkpoints ) )
        Sentinel::removeCheckpoint( 'throttle' );

    } else {
      // disable checkpoints
      Sentinel::disableCheckpoints();
    }
  }


  // Register user
  public static function register( $credentials, $activate = null ) {
    // detect activation
    if ( is_null( $activate ) )
      $activate = self::$activate_on_signup;

    return $activate === true ?
      Sentinel::registerAndActivate( $credentials ) :
      Sentinel::register( $credentials );
  }


  // Log user in
  public static function login( $email, $pass, $remember = false ) {
    return Sentinel::authenticate([
      'email'    => $email
    , 'password' => $pass
    ], $remember );
  }


  // log user out
  public static function logout() {
    return Sentinel::logout();
  }


  // Checks if user is logged in
  public static function loggedIn() {
    return Sentinel::check();
  }


  // Get the instance of the current logged in user
  public static function get() {
    return Sentinel::getUser();
  }


  // Find user by email or id
  public static function find( $email_or_id ) {
    if ( is_numeric( $email_or_id ) )
      return Sentinel::findById( $email_or_id );

    return Sentinel::findByCredentials([ 'login' => $email_or_id ]);
  }


  // Generate password reset link
  public static function resetCode( $email ) {
    if ( $user = self::find( $email ) ) {
      // get reminder repo and clean old codes
      $reminder = Sentinel::getReminderRepository();
      $reminder->removeExpired();

      // generate new code
      return $reminder->create( $user )->code;

    } else {
      throw new UserNotFoundException( "Unable to find user for email $email" );
    }
  }


  // Reset password using code
  public function resetPassword( $email, $password, $code ) {
    if ( $user = self::find( $email ) ) {
      // get reminder repo
      $reminder = Sentinel::getReminderRepository();
      
      // test existance
      if ( $reminder->exists( $user, $code ) )
        return $reminder->complete( $user, $code, $password );

      throw new UserResetCodeNotValidException( "The password reset code $code is not or no longer valid" );

    } else {
      throw new UserNotFoundException( "Unable to find user for email $email" );
    }
  }

}


// Exceptions
class UserNotFoundException extends Exception {}
class UserResetCodeNotValidException extends Exception {}




















