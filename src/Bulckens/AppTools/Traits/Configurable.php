<?php

namespace Bulckens\AppTools\Traits;

use Illuminate\Support\Str;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Config;

trait Configurable {

  protected $config;
  protected $file;

  // App config getter
  public function config( $key = null, $default = null ) {
    // make sure config is loaded
    if ( ! $this->config ) {
      $this->config = new Config( App::env() );
      $this->config->load( $this->file() );
    }    

    // get the config instance
    if ( is_null( $key ) )
      return $this->config;

    // get a specific key
    return $this->config->get( $key, $default );
  }


  // Get filename
  public function file( $file = null ) {
    // act as getter
    if ( is_null( $file ) && func_num_args() == 0 ) {
      if ( isset( $this->file ) )
        return $this->file;

      $names = explode( '\\', get_class() );
      return Str::snake( end( $names ) ) . '.yml';
    }

    // clear stored data
    $this->config = null;

    if ( is_callable([ $this, 'reset' ]) )
      $this->reset();

    // act as setter
    $this->file = $file;

    return $this;
  }

}