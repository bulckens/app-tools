<?php

namespace Bulckens\AppTraits;

trait Configurable {

  protected static $config;

  // App config getter
  public function config( $key = null ) {
    // make sure config is loaded
    if ( ! self::$config ) {
      // load app config
      self::$config = new Config( $env );
      self::$config->load( self::$file );
    }    

    // get the config instance
    if ( is_null( $key ) )
      return self::$config;

    // get a specific key
    return self::$config->get( $key );
  }

}