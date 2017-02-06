<?php

namespace Bulckens\AppTools;

use Bulckens\AppTraits\Configurable;
use Bulckens\AppTraits\Grounded;

class App {

  use Configurable;
  use Grounded;

  protected static $app;
  protected static $modules;

  public function __construct( $env, $root = null ) {
    self::$app  = $this;
    self::$env  = $env;
    self::$root = $root;
  }


  // Run the app
  public function run() {
    // clear existing modules
    self::$modules = [];

    // get modules
    $modules = $this->config( 'modules' );

    // initialize database
    if ( in_array( 'database', $modules ) )
      $this->module( 'database', new Database() );

    // initialize view
    if ( in_array( 'view', $modules ) )
      $this->module( 'view', new View() );
    
    // initialize router
    if ( in_array( 'router', $modules ) ) {
      $router = new Router();
      $this->module( 'router', $router->run() );
    }

    return $this;
  }


  // Test cli environment
  public static function cli() {
    return php_sapi_name() == 'cli';
  }
  

  // Public representation as array
  public static function toArray() {
    return [
      'env' => self::env()
    ];
  }


  // Get app instance 
  public static function instance() {
    return self::$app;
  }


  // Set and get module instances
  public function module( $name, $module = null ) {
    if ( is_null( $module ) ) {
      if ( isset( self::$modules[$name] ) )
        return self::$modules[$name];
    }

    self::$modules[$name] = $module;
  }


  // Get the database module
  public function database() {
    return $this->module( 'database' );
  }


  // Get the router module
  public function router() {
    return $this->module( 'router' );
  }

  
  // Get the view module
  public function view() {
    return $this->module( 'view' );
  }

}