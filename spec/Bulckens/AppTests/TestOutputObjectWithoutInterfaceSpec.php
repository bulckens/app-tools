<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTests\TestOutputObjectWithoutInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestOutputObjectWithoutInterfaceSpec extends ObjectBehavior {

  function it_is_initializable() {
    $this->shouldHaveType( TestOutputObjectWithoutInterface::class );
  }

}