<?php

namespace Bulckens\AppTools;

use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Bulckens\CliTools\Style;
use Bulckens\Helpers\StringHelper;
use Bulckens\AppTools\Traits\Configurable;
use Bulckens\AppTools\Notifier\Notification;

class Notifier {

  use Configurable;

  protected $logger;
  protected $data = [];

  public function __construct() {
    // catch both errors and exceptions
    if ( ! App::env( 'dev' )) {
      set_error_handler( 'Bulckens\AppTools\Notifier\ErrorHandler::notify' );
      set_exception_handler( 'Bulckens\AppTools\Notifier\ErrorHandler::notify' );
    }

    // logger settings
    $env = App::env();
    $dir = App::root( 'log' );
    $file = App::root( "log/$env.log" );
    $level = $this->config( 'level' ) ?: 'DEBUG';

    // make sure directory exists
    if ( ! file_exists( $dir )) {
      mkdir( $dir, 0777, true );
    }

    // initialize handler
    $handler = new StreamHandler( $file, constant( "Monolog\Logger::$level" ));

    // initialize logger
    $this->logger = new Logger( 'app_tools_logger' );
    $this->logger->pushHandler( $handler );
  }


  // Get/set arbitrary application data
  public function data( $data = null ) {
    if ( is_null( $data ))
      return $this->data;

    $this->data = array_replace_recursive( $this->data, $data );

    return $this;
  }


  // Log message by type
  public function log( $message, $type = 'info' ) {
    if ( is_callable( [ $this, $type ] ))
      call_user_func( [ $this, $type ], $message );
    else
      throw new NotifierMissingMethodException( "Method $type() does not exist" );

  }


  // Send info
  public function info( $message ) {
    $this->logger->addInfo( StringHelper::stringify( $message ));
  }


  // Send highlight
  public function high( $message ) {
    $this->logger->addInfo( Style::end( Style::green( StringHelper::stringify( $message ))));
  }


  // Send warning
  public function warn( $message ) {
    $this->logger->warning( Style::end( Style::yellow( StringHelper::stringify( $message ))));
  }


  // Send error
  public function error( $error ) {
    // if no exception is passed, make one
    if ( is_string( $error ))
      $error = new Exception( $error );

    // mail message
    if ( $this->config( 'email' )) {
      new Notification( $error, $this );
    }

    // log error
    $this->logger->error( Style::end( Style::red( $error->getMessage())));
  }


}


// Exceptions
class NotifierMissingMethodException extends Exception {}
