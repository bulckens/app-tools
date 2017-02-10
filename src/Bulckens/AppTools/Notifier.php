<?php

namespace Bulckens\AppTools;

use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Bulckens\CliTools\Style;
use Bulckens\AppTools\Traits\Configurable;

class Notifier {

  use Configurable;

  public function __construct() {
    // Catch errors as well
    set_error_handler( 'Bulckens\AppTools\Notifier\ErrorHandler::notify' );
    set_exception_handler( 'Bulckens\AppTools\Notifier\ErrorHandler::notify' );
  }

}
