<?php

namespace Bulckens\AppTools\Notifier;

class ErrorHandler {

  // Slim framework compatibility
  public static function slim( $c ) {
    return function ( $req, $res, $exception ) use ( $c ) {
      // notify error
      Notifier::error( $exception );

      // render output
      return $c['response']->withStatus( 500 )
                           ->withHeader( 'Content-Type', 'text/html' )
                           ->write( View::error( $exception ) );
    };
  }

  // Send out notification
  public static function notify( $e, $errstr = null, $errfile = null, $errline = null ) {
    if ( is_int( $e ) ) {
      // error code not included in error_reporting
      if ( ! ( error_reporting() & $e ) ) return;

      // error
      $message = "[$e] $errstr";
      $trace   = debug_backtrace();

    } else {
      // exception
      $message = $e->getMessage();
      $trace   = $e->getTrace();
    }

    // build error instance
    $error = new Error( "ERROR $message", $trace );

    // render instance
    echo View::error( $error );

    // notify error
    Notifier::error( $error );

    die();
  }
  
}