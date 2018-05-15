<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTests\TestValidatorHelper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestValidatorHelperSpec extends ObjectBehavior {

  // Truthy method
  function it_is_truthy() {
    $this::truthy()->shouldBe( true );
  }

  // Falsy method
  function it_is_falsy() {
    $this::falsy()->shouldBe( false );
  }

}
