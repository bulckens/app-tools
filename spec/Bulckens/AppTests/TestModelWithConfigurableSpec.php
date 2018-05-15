<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTools\App;
use Bulckens\AppTests\TestModelWithConfigurable;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestModelWithConfigurableSpec extends ObjectBehavior {

  function let() {
    new App( 'dev' );
  }
  
  function it_uses_the_app_environment() {
    $this->config( 'frolick' )->shouldBe( 'fun' );
  }

}
