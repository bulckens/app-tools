<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTests\TestApp;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestAppSpec extends ObjectBehavior {

  function let() {
    $this->beConstructedWith( 'dev' );
  }
  
  // Customize method
  function it_gets_customized_on_run() {
    $this->run()->customized()->shouldBe( true );
  }

  function it_returns_itself_after_customizing() {
    $this->customize()->shouldBe( $this );
  }

}
