<?php

namespace Bulckens\AppTools\Traits;

use Exception;

trait Modulized {

  protected static $instance;
  protected static $modules;

  // Get app instance 
  public static function get() {
    return self::$instance;
  }


  // Set and get module instances
  public function module( $name, $module = null ) {
    if ( is_null( $module ) ) {
      if ( isset( self::$modules[$name] ) )
        return self::$modules[$name];
    } else {
      // set module
      self::$modules[$name] = $module;

      // regiter module if if is not a bundled one
      if ( ! in_array( $name, self::$available_modules ) )
        array_push( self::$available_modules, $name );

      return $this;
    }
  }

}


// Exceptions
class ModuleNotFoundException extends Exception {}