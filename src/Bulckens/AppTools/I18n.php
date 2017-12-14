<?php

namespace Bulckens\AppTools;

use Exception;
use Symfony\Component\Yaml\Yaml;
use Bulckens\Helpers\FileHelper;

class I18n {

  use Traits\Configurable;
  use Traits\Cacheable;
  use Traits\Diggable;

  protected $locale;


  public function __construct( $options = [] ) {
    // set customconfig file
    if ( is_array( $options ) && isset( $options['config'] ) ) {
      $this->configFile( $options['config'] );
    }

    // check existance of locale dirt
    if ( ! file_exists( $dir = $this->dir() ) ) {
      throw new I18nDirMissingException( "Locales dir '$dir' does not exist" );
    }

    // default locale
    $locale = $this->config( 'default', 'en' );

    // get locale
    if ( is_array( $options ) ) {
      // define locale
      $locale = isset( $options['locale'] ) ? $options['locale'] : $locale;

    } elseif ( is_string( $options ) ) {
      $locale = $options;
    }

    // store locale
    $this->locale( $locale );
  }
  
  
  // Get/set locale
  public function locale( $locale = null ) {
    // act as getter
    if ( is_null( $locale ) ) {
      return $this->locale;
    }

    // reference current situation
    $old_locale = $this->locale;

    // purge cache
    if ( $locale != $old_locale ) $this->purgeCache();

    // act as setter
    $this->locale = $locale;

    // reload locales
    if ( $locale != $old_locale ) $this->load();
    
    return $this;
  }


  // Get cache id
  public function cacheId() {
    return $this->locale();
  }


  // Translate key
  public function t( $key, $default = null ) {
    return $this->get( $key, $default );
  }


  // Cache dir
  public function dir() {
    $dir = $this->config( 'dir', 'i18n' );

    return preg_match( '/^\//', $dir ) ?
      $dir : App::root( App::env( 'dev' ) ? "dev/$dir" : $dir );
  }


  // Load locales
  protected function load() {
    // load
    if ( ! $this->cached() ) {
      $locale = $this->locale();

      // find locale files
      $files = FileHelper::rsearch( $this->dir(), "/.*\.$locale\.ya?ml/" );

      // load locales
      $diggable = [];

      foreach ( $files as $file ) {
        $locales = Yaml::parse( file_get_contents( $file ) );

        $diggable = array_replace_recursive( $diggable, $locales );
      }

      // cache locales
      $this->cache( $diggable );
    }

    // store diggable
    $this->diggable = $this->cache();
  }

}


// Exceptions
class I18nDirMissingException extends Exception {}
