<?php

namespace Bulckens\AppTools;

use Exception;
use Symfony\Component\Yaml\Yaml;

class Config {

  protected $config;

  public function __construct( $env ) {
    $this->env = $env;
  }
  

  // Load config file
  public function load( $file, $path = 'config' ) {
    // make sure to set testing environment dir if required
    if ( App::env( 'test' ) && $path == 'config' )
      $path = 'dev/config';

    // get full config path
    $file = App::root( "$path/$file" );

    if ( file_exists( $file ) ) {
      $config = Yaml::parse( file_get_contents( $file ) );

      if ( isset( $config[$this->env] ) )
        $this->config = $config[$this->env];
      else
        throw new ConfigEnvironmentMissingException( "Environment $this->env could not be found" );
      
    } else {
      throw new ConfigFileMissingException( "Config file $file could not be found" );
    }

    return $this;
  }


  // Get config value by given key
  public function get( $key ) {
    if ( isset( $this->config[$key] ) )
      return $this->config[$key];
  }

}

// Exceptions
class ConfigFileMissingException extends Exception {};
class ConfigEnvironmentMissingException extends Exception {};