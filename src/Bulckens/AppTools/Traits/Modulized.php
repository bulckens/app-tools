<?php

namespace Bulckens\AppTools\Traits;

use Exception;

trait Modulized {

  protected static $instance;
  protected static $modules = [];

  // Get app instance 
  public static function get() {
    return self::$instance;
  }


  // Clear modules
  public function clear( $force = false ) {
    if ( $force )
      self::$modules = [];
    else
      foreach ( self::$bundled_modules as $name )
        unset( self::$modules[$name] );

    return $this;
  }


  // Set and get module instances
  public function module( $name, $module = null ) {
    if ( is_null( $module ) ) {
      if ( isset( self::$modules[$name] ) )
        return self::$modules[$name];
    } else {
      // set module
      self::$modules[$name] = $module;

      return $this;
    }
  }


  // get registered modules
  public function modules() {
    return array_keys( self::$modules );
  }

}


// Exceptions
class ModuleNotFoundException extends Exception {}