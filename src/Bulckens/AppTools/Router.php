<?php

namespace Bulckens\AppTools;

use Exception;
use Slim\App as Slim;
use Slim\Container;
use Bulckens\AppTools\Notifier\ErrorHandler;
use Bulckens\AppTools\Traits\Configurable;

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

    // initialize container
    $this->c = new Container( $config );
    
    // get custom error handler
    $handler = $this->config( 'handler' );

    // add 500 handler
    if ( $handler && is_callable( "$handler::error" ) ) {
      $this->error( "$handler::error" );
    } else {
      $this->error( function( $c ) {
        return ErrorHandler::slim( $c );
      });
    }
      
    // add 404 handler
    if ( $handler && is_callable( "$handler::notFound" ) )
      $this->notFound( "$handler::notFound" );

    // add 405 handler
    if ( $handler && is_callable( "$handler::notAllowed" ) )
      $this->notAllowed( "$handler::notAllowed" );
  }


  // Add 500 handler
  public function error( $handler ) {
    $this->c['errorHandler'] = $handler;

    return $this;
  }
  

  // Add 404 handler
  public function notFound( $handler ) {
    $this->c['notFoundHandler'] = $handler;

    return $this;
  }

  // Add 405 handler
  public function notAllowed( $handler ) {
    $this->c['notAllowedHandler'] = $handler;

    return $this;
  }


  // Run app
  public function run() {
    if ( $root = $this->config( 'root' ) ) {
      // reference app instance
      $app = App::get();

      // alias view
      $view = $app->view();

      // initialize new slim app
      $route = new Slim( $this->c );

      // pre-load routes
      foreach ( glob( App::root( "$root/*Routes.php" ) ) as $routes )
        require_once( $routes );
      
      // run the app
      $route->run( App::env( 'dev', 'test' ) );

      return $this;

    } else {
      throw new RouterRoutesRootNotDefinedException( 'Router routes root is not defined in ' . $this->file() );
    }
  }

}

// Exceptions
class RouterRoutesRootNotDefinedException extends Exception {};