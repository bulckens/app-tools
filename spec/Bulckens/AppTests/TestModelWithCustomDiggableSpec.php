<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTools\App;
use Bulckens\AppTests\TestModelWithCustomDiggable;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestModelWithCustomDiggableSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
  }


  // Get method
  function it_gets_a_root_key() {
    $this->get( 'apparatus' )
      ->shouldBe( 'Flying Spaghetti Monster' );
  }

  function it_gets_a_nested_key() {
    $this->get( 'first.second.apparatus' )
      ->shouldBe( 'pastover' );
  }
}
