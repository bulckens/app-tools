<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTools\App;
use Bulckens\AppTests\TestModelWithEnvironmentalized;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestModelWithEnvironmentalizedSpec extends ObjectBehavior {
  
  function let() {
    new App( 'dev' );
  }
  
  function it_uses_the_app_environment() {
    $this->beConstructedWith( 'full' );
    $this->config( 'fabulous' )->shouldBe( 'feasts' );
  }

}
