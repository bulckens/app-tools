<?php

namespace Bulckens\AppTools\Notifier;

use Exception;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Notifier;

class ErrorHandler {

  // Slim framework compatibility
  public static function slim( $c ) {
    return function ( $req, $res, $exception ) use ( $c ) {
      // reference notifier
      $notifier = App::get()->notifier();
      
      // notify error
      $notifier->error( $exception );

      // prepare message
      $render = new Render( $exception, $notifier );

      // render output
      return $c['response']
        ->withStatus( 500 )
        ->withHeader( 'Content-Type', 'text/html' )
        ->write( $render->html( $exception->getMessage() ) );
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

    // reference notifier
    $notifier = App::get()->notifier();

    // build error instance
    $error = new Error( "ERROR $message", $trace );

    // prepare message
    $render = new Render( $error, $notifier );

    // render message
    echo App::cli() ? $render->cli( $message ) : $render->html( $message );

    // notify error
    if ( $notifier ) {
      $notifier->error( $error );
    } else {
      throw new Exception( $message );
    }

    die();
  }
  
}