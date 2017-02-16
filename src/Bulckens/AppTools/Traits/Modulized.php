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
    }

    self::$modules[$name] = $module;
  }

}


// Exceptions
class ModuleNotFoundException extends Exception {}