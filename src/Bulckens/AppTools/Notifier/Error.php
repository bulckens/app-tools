<?php

namespace Bulckens\AppTools\Notifier;

class Error {

  protected $message;
  protected $backtrace;

  public function __construct( $message, $backtrace ) {
    $this->message   = $message;
    $this->backtrace = $backtrace;
  }

  // Get error message
  public function getMessage() {
    return $this->message;
  }
  
  // Get full backtrace
  public function getTrace() {
    return $this->backtrace;
  }

}