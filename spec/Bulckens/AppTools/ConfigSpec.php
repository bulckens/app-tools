<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\Config;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConfigSpec extends ObjectBehavior {

  function let() {
    $this->beConstructedWith( 'test' );
    $this->load( 'test.yml' );
  }


  // Load method
  function it_loads_the_default_config_file() {
    $this->load( 'load.yml' )->get( 'fladd' )->shouldBe( 'rrer' );
  }

  function it_returns_itself_after_loading_the_default_config_file() {
    $this->load( 'load.yml' )->shouldBe( $this );
  }


  // Get method
  function it_returns_the_value_for_the_given_key() {
    $this->get( 'key' )->shouldBe( 'value' );
  }
  
}
