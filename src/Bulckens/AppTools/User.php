<?php

namespace Bulckens\AppTools;

use Exception;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Cartalyst\Sentinel\Users\EloquentUser;
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
  public static function register( array $credentials, $activate = null ) {
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
  public static function find( $user ) {
    if ( ! is_a( $user, EloquentUser::class ) ) {
      if ( is_numeric( $user ) )
        $user = Sentinel::findById( $user );
      else
        $user = Sentinel::findByCredentials([ 'login' => $user ]);
    }
    
    // return user if found
    if ( $user ) return $user;
    
    throw new UserNotFoundException( 'Unable to find user' );
  }


  // Generate password reset link
  public static function resetCode( $user ) {
    // find user or fail
    $user = self::find( $user );
      
    // get reminder repo and clean old codes
    $reminder = Sentinel::getReminderRepository();
    $reminder->removeExpired();

    // generate new code
    return $reminder->create( $user )->code;
  }


  // Reset password using code
  public function resetPassword( $user, $password, $code ) {
    // find user or fail
    $user = self::find( $user );

    // get reminder repo
    $reminder = Sentinel::getReminderRepository();
    
    // test existance
    if ( $reminder->exists( $user, $code ) )
      return $reminder->complete( $user, $code, $password );

    throw new UserResetCodeNotValidException( "The password reset code $code is not or no longer valid" );
  }

}


// Exceptions
class UserNotFoundException extends Exception {}
class UserResetCodeNotValidException extends Exception {}
