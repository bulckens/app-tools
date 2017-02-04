<?php

namespace Bulckens\AppTools;

use Exception;
use Bulckens\AppTraits\Configurable;

class App {

  use Configurable;

  protected static $env;
  protected static $root;
  protected static $file = 'app.yml';

  public function __construct( $env, $root = null ) {
    self::$env    = $env;
    self::$root   = $root;
  }


  // Get host project root
  public static function root( $path = '' ) {
    if ( ! self::$root ) {
      // find root based on the location of the config folder
      self::$root = __DIR__;
      $depth = 0;
      
      // find root dir
      while ( ! file_exists( self::$root . '/config/' . self::$file ) && ! file_exists( self::$root . '/dev/config/' . self::$file ) && $depth < 20 ) {
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

    return $test == self::$env;
  }


  // Test cli environment
  public function cli() {
    return php_sapi_name() == 'cli';
  }

}

// Exceptions
class RootNotFoundException extends Exception {};