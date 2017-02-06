<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTests\TestHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestHandlerSpec extends ObjectBehavior {

  function it_is_initializable() {
    $this->shouldHaveType(TestHandler::class);
  }
  
}
