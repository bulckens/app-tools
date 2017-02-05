<?php

namespace Bulckens\AppTools;

use Exception;
use Slim\App as Slim;
use Slim\Container;
use Bulckens\Notifier\ErrorHandler;
use Bulckens\AppTraits\Configurable;

class Router {

  use Configurable;

  protected $c;

  public function __construct() {
    // define environment-specific settings
    if ( $this->config( 'debug' ) ) {
      $config = [];
    } else {
      $config = [
        'settings' => [
          'displayErrorDetails' => true
        ]
      ];
    }

    // add custom error handler
    $this->c = new Container( $config );
    $this->c['errorHandler'] = function( $c ) {
      return ErrorHandler::slim( $c );
    };
  }


  // Add 404 handler
  public function notFound( $handler ) {
    $this->c['notFoundHandler'] = $handler;

    return $this;
  }


  // Run app
  public function run() {
    if ( $root = $this->config( 'root' ) ) {
      // reference app instance
      $app = App::instance();

      // initialize new slim app
      $route = new Slim( $this->c );

      // pre-load routes
      foreach ( glob( App::root( "$root/*Routes.php" ) ) as $routes )
        require_once( $routes );

      // run the app
      $route->run();

      return $this;

    } else {
      throw new RouterRoutesRootNotDefinedException( 'Router routes root is not defined in ' . $this->file() );
    }
  }

}

// Exceptions
class RouterRoutesRootNotDefinedException extends Exception {};