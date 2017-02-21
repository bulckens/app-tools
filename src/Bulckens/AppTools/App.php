<?php

namespace Bulckens\AppTools;

use Exception;
use Illuminate\Support\Str;
use Bulckens\Helpers\FileHelper;
use Bulckens\AppTools\Traits\Modulized;
use Bulckens\AppTools\Traits\Configurable;

class App {

  use Modulized;
  use Configurable;
  
  protected static $env;
  protected static $root;

  // available modules (in order of initialization)
  protected static $available_modules = [ 'notifier', 'database', 'user', 'cache', 'view' ];


  public function __construct( $env, $root = null, $up = null ) {
    self::$instance = $this;
    self::$env      = $env;

    self::$root = is_null( $up ) ? $root : FileHelper::parent( $root, $up );
  }


  // Dynamic methods
  public function __call( $name, $arguments ) {
    // named module methods
    if ( $name == 'router' || in_array( $name, self::$available_modules ) )
      return $this->module( $name );

    // reset callback (not necessary on this class)
    elseif ( $name == 'reset')
      return;

    throw new AppMethodMissingException( "Missing method " . self::class . "::$name" );
  }


  // Run the app
  public function run() {
    // clear existing modules
    self::$modules = [];

    // get modules
    $modules = $this->config( 'modules' );

    // initialize modules
    foreach ( self::$available_modules as $module ) {
      // initialize module if required
      if ( in_array( $module, $modules ) ) {
        // get class name for module
        $class = Str::studly( $module );
        $class = "Bulckens\AppTools\\$class";

        // register module
        $this->module( $module, new $class() );
      }
    }
    
    // initialize router
    if ( in_array( 'router', $modules ) ) {
      $router = new Router();
      $this->module( 'router', $router->run() );
    }

    return $this;
  }


  // Get host project root
  public static function root( $path = '' ) {
    if ( ! self::$root ) {
      // find root based on the location of the config folder
      self::$root = __DIR__;
      $depth = 0;

      // get current config dir
      $dir = self::env( 'dev' ) ? '/dev/config/' : '/config/';
      
      // find root dir
      while ( ! file_exists( self::$root . $dir ) && $depth < 20 ) {
        // detect capistrano installation
        if ( basename( dirname( self::$root ) ) == 'shared' )
          self::$root = dirname( dirname( self::$root ) ) . '/current';
        else
          self::$root = dirname( self::$root );

        $depth++;
      }
    }

    if ( self::$root == '/' )
      throw new RootNotFoundException( 'Project root ' . self::$root . ' is not acceptable' );

    return str_replace( '//', '/', self::$root . "/$path" );
  }


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
    if ( file_exists( $version = self::root( 'config/.version' ) ) )
      return file_get_contents( $version );

    // build command
    $command = 'git describe --abbrev=0 --tags';

    // check for capistrano installation
    $path = dirname( self::root() ) . "/shared/cached-copy";

    if ( file_exists( $path ) )
      $command = "cd $path && $command";

    // get raw version from git
    $version = exec( $command );

    // cache version for capistrano installations
    if ( file_exists( $path ) )
      file_put_contents( self::root( 'config/.version' ), $version );

    return $version;
  }

}

// Exceptions
class RootNotFoundException extends Exception {};
class AppMethodMissingException extends Exception {};