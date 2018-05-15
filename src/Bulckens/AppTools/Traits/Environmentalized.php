<?php

namespace Bulckens\AppTools\Traits;

trait Environmentalized {

  protected static $env;

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

}