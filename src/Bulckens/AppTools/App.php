<?php

namespace Bulckens\AppTools;

use Exception;
use Bulckens\Helpers\FileHelper;
use Bulckens\AppTools\Traits\Modulized;
use Bulckens\AppTools\Traits\Configurable;

class App {

  use Modulized;
  use Configurable;
  
  protected static $env;
  protected static $root;

  public function __construct( $env, $root = null, $up = null ) {
    self::$instance = $this;
    self::$env      = $env;

    self::$root = is_null( $up ) ? $root : FileHelper::parent( $root, $up );
  }


  // Run the app
  public function run() {
    // clear existing modules
    self::$modules = [];

    // get modules
    $modules = $this->config( 'modules' );

    // initialize notifier
    if ( in_array( 'notifier', $modules ) )
      $this->module( 'notifier', new Notifier() );

    // initialize database
    if ( in_array( 'database', $modules ) )
      $this->module( 'database', new Database() );

    // initialize user
    if ( in_array( 'user', $modules ) )
      $this->module( 'user', new User() );

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


  // Get host project root
  public static function root( $path = '' ) {
    if ( ! self::$root ) {
      // find root based on the location of the config folder
      self::$root = __DIR__;
      $depth = 0;

      // get current config dir
      $dir = self::env( 'dev' ) ? '/dev/config/' : '/config/';
      
      // find root dir
      while ( ! file_exists( self::$root . $dir ) && $depth < 20 ) {
        // detect capistrano installation
        if ( basename( dirname( self::$root ) ) == 'shared' )
          self::$root = dirname( dirname( self::$root ) ) . '/current';
        else
          self::$root = dirname( self::$root );

        $depth++;
      }
    }

    if ( self::$root == '/' )
      throw new RootNotFoundException( 'Project root ' . self::$root . ' is not acceptable' );

    return str_replace( '//', '/', self::$root . "/$path" );
  }


  // Test current environment
  public static function env( $test = null ) {
    if ( count( $arguments = func_get_args() ) > 1 )
      return self::env( $arguments );

    elseif ( is_null( $test ) )
      return self::$env;

    elseif ( is_array( $test ) )
      return in_array( self::$env, $test );


    return $test == self::$env;
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


  // Get the database module
  public function database() {
    return $this->module( 'database' );
  }


  // Get the notifier module
  public function notifier() {
    return $this->module( 'notifier' );
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

// Exceptions
class RootNotFoundException extends Exception {};