<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTests\TestOutputObject;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestOutputObjectSpec extends ObjectBehavior {

  function it_is_initializable() {
    $this->shouldHaveType( TestOutputObject::class );
  }

}