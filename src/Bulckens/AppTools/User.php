<?php

namespace Bulckens\AppTools;

use Exception;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;
use Bulckens\AppTools\Traits\Configurable;

class User {

  use Configurable;

  protected static $activate_at_login = false;

  // Initialize database connection
  public function __construct() {
    // setup a new Eloquent Capsule instance
    $capsule = new Capsule;
    
    $capsule->addConnection([
      'driver'    => 'mysql'
    , 'host'      => $this->config( 'host' )
    , 'database'  => $this->config( 'name' )
    , 'username'  => $this->config( 'user' )
    , 'password'  => $this->config( 'password' )
    , 'charset'   => $this->config( 'charset' )
    , 'collation' => $this->config( 'collate' )
    ]);

    $capsule->bootEloquent();

    // setup chckpoints checkpoints
    Sentinel::enableCheckpoints();

    // configure activation checkpoint
    if ( in_array( 'activation', $this->config( 'checkpoints' ) ) )
      self::$activate_at_login = $this->config( 'login.activate', false );
    else
      Sentinel::removeCheckpoint( 'activation' );

    // configure activation checkpoint
    if ( ! in_array( 'throttle', $this->config( 'checkpoints' ) ) )
      Sentinel::removeCheckpoint( 'throttle' );
  }


  // Register user
  public static function register( $email, $pass, $activate = false ) {
    $credentials = [
      'email'    => $email
    , 'password' => $pass
    ];

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
  public static function find( $id ) {
    if ( is_numeric( $id ) )
      return Sentinel::findById( $id );

    return Sentinel::findByCredentials([ 'login' => $id ]);
  }


  // Generate password reset link
  public static function resetCode( $email ) {
    if ( $user = self::find( $email ) ) {
      $reminder = Sentinel::getReminderRepository()->create( $user );
      return $reminder->code;

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




















