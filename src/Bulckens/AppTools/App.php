<?php

namespace Bulckens\AppTools;

use Exception;
use Illuminate\Support\Str;
use Bulckens\Helpers\FileHelper;
use Bulckens\Helpers\ArrayHelper;

class App {

  use Traits\Modulized;
  use Traits\Configurable;
  use Traits\Environmentalized;

  protected static $root;

  // available modules (in order of initialization)
  protected static $bundled_modules = [
    'cache'
  , 'database'
  , 'i18n'
  , 'notifier'
  , 'router'
  , 'statistics'
  , 'user'
  , 'view'
  , 'worker'
  ];


  public function __construct( $env, $root = null, $up = null ) {
    self::$instance = $this;
    self::$env = $env;

    self::$root = is_null( $up ) ? $root : FileHelper::parent( $root, $up );
  }


  // Dynamic methods
  public function __call( $name, $arguments ) {
    // get all bundled and registered modules
    $modules = array_merge( self::$bundled_modules, $this->modules());

    // named module methods
    if ( in_array( $name, $modules )) {
      return $this->module( $name );

    // reset callback (not necessary on this class)
    } elseif ( $name == 'reset') {
      return;
    }

    throw new AppMethodMissingException( 'Missing method ' . self::class . "::$name" );
  }


  // Run the app
  public function run() {
    // clear existing modules
    $this->clear();

    // get configured modules and always include statistics
    $configured = array_merge([
      'statistics'
    ], $this->config( 'modules', [] ));

    // create a module class map
    $modules = [];

    foreach ( $configured as $module ) {
      if ( is_array( $module )) {
        $modules = array_merge( $modules, $module );
      } else {
        $modules[$module] = 'Bulckens\AppTools\\' . Str::studly( $module );
      }
    }

    // initialize modules
    foreach ( self::$bundled_modules as $module ) {
      if ( isset( $modules[$module] )) {
        $class = $modules[$module];
        $this->module( $module, new $class());
      }
    }

    // run customize method, if present
    if ( method_exists( $this, 'customize' )) {
      $this->customize();
    }

    // run router
    if ( isset( $modules['router'] ) && ! self::cli()) {
      $this->router()->run();
    }

    return $this;
  }


  // Get host project root
  final public static function root( $path = '' ) {
    if ( ! self::$root ) {
      // find root based on the location of the config folder
      self::$root = __DIR__;
      $depth = 0;

      // get current config dir
      $dir = self::env( 'dev' ) ? '/dev/config/' : '/config/';

      // find root dir
      while ( ! file_exists( self::$root . $dir ) && $depth < 20 ) {
        // detect capistrano installation
        if ( basename( dirname( self::$root )) == 'shared' ) {
          self::$root = dirname( dirname( self::$root )) . '/current';
        } else {
          self::$root = dirname( self::$root );
        }

        $depth++;
      }
    }

    if ( self::$root == '/' ) {
      throw new RootNotFoundException( 'Project root ' . self::$root . ' is not acceptable' );
    }

    return str_replace( '//', '/', self::$root . "/$path" );
  }


  // Test cli environment
  public static function cli() {
    return php_sapi_name() == 'cli';
  }


  // Public representation as array
  public static function toArray() {
    return [
      'env'     => self::env()
    , 'version' => self::version()
    ];
  }


  // Get version tag of project
  public static function version() {
    // check for version file
    if ( file_exists( $version = self::root( 'config/.version' )) ){
      return file_get_contents( $version );
    }

    // build command
    $command = 'git describe --abbrev=0 --tags';

    // check for capistrano installation
    $root = dirname( self::root());
    $cap2_path = "$root/shared/cached-copy";
    $cap3_path = "$root/repo";

    if ( file_exists( $cap3_path )) {
      $command = "cd $cap3_path && $command";
    } elseif ( file_exists( $cap2_path )) {
      $command = "cd $cap2_path && $command";
    }

    // get raw version from git
    $version = exec( $command );

    // cache version for capistrano installations
    if ( file_exists( $cap3_path ) || file_exists( $cap2_path )) {
      file_put_contents( self::root( 'config/.version' ), $version );
    }

    return $version;
  }

}

// Exceptions
class RootNotFoundException extends Exception {};
class AppMethodMissingException extends Exception {};
