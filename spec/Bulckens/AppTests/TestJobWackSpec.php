<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTests\TestJobWack;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestJobWackSpec extends ObjectBehavior {

  function it_is_initializable() {
    $this->shouldHaveType( TestJobWack::class );
  }

}
