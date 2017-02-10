<?php

namespace Bulckens\AppTools;

use Exception;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;
use Bulckens\AppTools\Traits\Configurable;

class User {

  use Configurable;

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
  }


  // Register user
  public function register( $email, $pass ) {
    return Sentinel::register([
      'email'    => $email
    , 'password' => $pass
    ]);
  }


  // Authenticate user
  public function auth( $email, $pass, $remember = false ) {
    return Sentinel::authenticate([
      'email'    => $email
    , 'password' => $pass
    ], $remember );
  }


  // Checks if user is logged in
  public function loggedIn() {
    return Sentinel::check();
  }


  // Get user instance
  public function get() {
    return Sentinel::getUser();
  }

}
