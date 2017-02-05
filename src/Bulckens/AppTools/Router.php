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
      // initialize new slim app
      $app = new Slim( $this->c );

      // pre-load routes
      foreach ( glob( App::root( "$root/*Routes.php" ) ) as $route )
        require_once( $route );

      // run the app
      $app->run();

    } else {
      throw new RouterRoutesRootNotDefinedException( 'Router routes root is not defined in ' . $this->file() );
    }
  }

}

// Exceptions
class RouterRoutesRootNotDefinedException extends Exception {};