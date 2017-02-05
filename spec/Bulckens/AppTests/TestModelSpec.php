<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTests\TestModel;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestModelSpec extends ObjectBehavior {

  function it_is_initializable() {
    $this->shouldHaveType(TestModel::class);
  }
  
}
