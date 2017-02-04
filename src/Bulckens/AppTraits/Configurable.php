<?php

namespace Bulckens\AppTraits;

use Bulckens\AppTools\App;
use Bulckens\AppTools\Config;

trait Configurable {

  protected static $config;

  // App config getter
  public function config( $key = null ) {
    // make sure config is loaded
    if ( ! self::$config ) {
      // load app config
      self::$config = new Config( App::env() );
      self::$config->load( self::$file );
    }    

    // get the config instance
    if ( is_null( $key ) )
      return self::$config;

    // get a specific key
    return self::$config->get( $key );
  }

}