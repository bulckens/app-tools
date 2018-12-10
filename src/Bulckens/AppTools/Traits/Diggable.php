<?php

namespace Bulckens\AppTools\Traits;

trait Diggable {

  protected $diggable;

  // Get config value by given key
  public function get( $key, $default = null, $force = false ) {
    // get diggable key
    $diggable_key = isset( $this->diggable_key ) ? $this->diggable_key : 'diggable';

    // prepare path iteration
    $parts = explode( '.', $key );
    $value = $this->$diggable_key;

    // find value for path
    foreach ( $parts as $part ) {
      if ( isset( $value[$part] ) ) {
        $value = $value[$part];
      } else {
        return $force && is_null( $default ) ?
          ( is_string( $force ) ? "$force$key" : $key) : $default;
      }
    }
    
    return $value;
  }

}