<?php

namespace spec\Bulckens\AppTools\Notifier;

use stdClass;
use Exception;
use Slim\Http\Response;
use Bulckens\Helpers\FileHelper;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Notifier\ErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErrorHandlerSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev', FileHelper::parent( __DIR__, 4 ) );
    $app->run();
  }
  
  // Slim
  function it_returns_an_error_handler_for_slim() {
    $std = new stdClass();
    $exception = new Exception( 'Fake error' );

    $handler = $this::slim([ 'response' => new Response( 200 ) ]);
    $handler( $std, $std, $exception )->shouldHaveType( 'Slim\Http\Response' );
  }

}
