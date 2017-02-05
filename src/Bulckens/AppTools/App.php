<?php

namespace Bulckens\AppTools;

use Exception;
use Bulckens\AppTraits\Configurable;

class App {

  use Configurable;

  protected static $app;
  protected static $env;
  protected static $root;

  public function __construct( $env, $root = null ) {
    self::$app  = $this;
    self::$env  = $env;
    self::$root = $root;
  }


  // Get host project root
  public static function root( $path = '' ) {
    if ( ! self::$root ) {
      // find root based on the location of the config folder
      self::$root = __DIR__;
      $depth = 0;

      // get current config dir
      $dir = self::env( 'test' ) ? '/dev/config/' : '/config/';
      
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
    if ( is_null( $test ) )
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


  // Get app instance 
  public static function instance() {
    return self::$app;
  }

}

// Exceptions
class RootNotFoundException extends Exception {};