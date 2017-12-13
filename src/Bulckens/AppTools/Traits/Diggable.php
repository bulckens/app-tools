<?php

namespace Bulckens\AppTools\Traits;

trait Diggable {

  protected $diggable;

  // Get config value by given key
  public function get( $key, $default = null ) {
    // prepare path iteration
    $parts = explode( '.', $key );
    $value = $this->diggable;

    // find value for path
    foreach ( $parts as $part ) {
      if ( isset( $value[$part] ) ) {
        $value = $value[$part];
      } else {
        return $default;
      }
    }
    
    return $value;
  }

}