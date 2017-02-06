<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\Config;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConfigSpec extends ObjectBehavior {

  // Load method
  function it_loads_the_default_config_file() {
    $this->beConstructedWith( 'test' );
    $this->load( 'load.yml' )->get( 'fladd' )->shouldBe( 'rrer' );
  }

  function it_returns_itself_after_loading_the_default_config_file() {
    $this->beConstructedWith( 'test' );
    $this->load( 'load.yml' )->shouldBe( $this );
  }


  // Get method
  function it_returns_the_value_for_the_given_key() {
    $this->beConstructedWith( 'test' );
    $this->load( 'test.yml' );
    $this->get( 'key' )->shouldBe( 'value' );
  }

  function it_returns_the_default_value_if_the_given_key_could_not_be_found() {
    $this->beConstructedWith( 'test' );
    $this->load( 'test.yml' );
    $this->get( 'fallalalallala', 81 )->shouldBe( 81 );
  }


  // Root method
  function it_finds_the_project_root() {
    $this->beConstructedWith( 'test' );
    $this->load( 'test.yml' );
    $this->root()->shouldEndWith( '/app-tools/' );
  }
  

  // Env method
  function it_returns_the_defined_environment() {
    $this->beConstructedWith( 'test' );
    $this->load( 'test.yml' );
    $this->env()->shouldBe( 'test' );
  }

  function it_tests_positive_with_the_current_environment() {
    $this->beConstructedWith( 'test' );
    $this->load( 'test.yml' );
    $this->env( 'test' )->shouldBe( true );
  }

  function it_tests_negative_with_the_wrong_environment() {
    $this->beConstructedWith( 'test' );
    $this->load( 'test.yml' );
    $this->env( 'labs' )->shouldBe( false );
  }

  function it_tests_positive_with_the_current_environment_against_multiple_given() {
    $this->beConstructedWith( 'test' );
    $this->load( 'test.yml' );
    $this->env([ 'test', 'development' ])->shouldBe( true );
  }
  
}
