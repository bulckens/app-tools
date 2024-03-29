<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\App;
use Bulckens\AppTools\Config;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConfigSpec extends ObjectBehavior {

  function let() {
    new App( 'dev' );
    $this->beConstructedWith( 'dev' );
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

  function it_returns_the_value_for_the_given_nested_key() {
    $this->get( 'nested.key' )->shouldBe( 'other value' );
  }

  function it_returns_null_if_the_value_is_could_not_be_found_with_given_key() {
    $this->get( 'non_existant' )->shouldBe( null ); 
  }

  function it_returns_null_if_the_value_is_could_not_be_found_with_given_nested_key() {
    $this->get( 'nested.non_existant' )->shouldBe( null ); 
  }

  function it_returns_the_default_value_if_the_given_key_could_not_be_found() {
    $this->get( 'fallalalallala', 81 )->shouldBe( 81 );
  }
  
}
