<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTools\App;
use Bulckens\AppTests\TestModelWithDiggable;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestModelWithDiggableSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
  }
  
  // Get method
  function it_gets_a_root_key() {
    $this->get( 'apparatus' )
      ->shouldBe( 'time travel machine' );
  }

  function it_gets_a_nested_key() {
    $this->get( 'first.second.apparatus' )
      ->shouldBe( 'felange' );
  }

  function it_defaults_to_a_given_value_if_the_key_could_not_be_found() {
    $this->get( 'first.second.third.apparatus', 'gyrocar' )
      ->shouldBe( 'gyrocar' );
  }

  function it_returns_the_key_if_explicitly_required() {
    $this->get( 'first.second.third.apparatus', null, true )
      ->shouldBe( 'first.second.third.apparatus' );
  }

  function it_returns_the_key_with_a_prefix_if_explicitly_required() {
    $this->get( 'first.second.third.apparatus', null, 'missing: ' )
      ->shouldBe( 'missing: first.second.third.apparatus' );
  }

}
