<?php

namespace spec\Bulckens\AppTools\Notifier;

use stdClass;
use Exception;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Notifier\ErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErrorHandlerSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev', dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) );
    $app->run();
  }
  
  // Slim
  function it_returns_an_error_handler_for_slim() {
    $std = new stdClass();
    $exception = new Exception( 'Fake error' );

    $handler = $this::slim([]);
    $handler( $std, $std, $exception )->shouldHaveType( 'floo' );
  }

}
