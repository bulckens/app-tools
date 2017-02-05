<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\Router;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RouterSpec extends ObjectBehavior {

  function letGo() {
    $this->file( null );
  }


  // Run method
  function it_returns_itself_after_initializing() {
    $this->notFound( function( $c ) {
      return function( $res, $req ) use( $c ) {
        return $c['response']->write( '' );
      };
    });
    $this->run()->shouldBe( $this );
  }

  function it_fails_to_run_when_no_view_root_is_defined() {
    $this->file( 'router_fail.yml' );
    $this->shouldThrow( 'Bulckens\AppTools\RouterRoutesRootNotDefinedException' )->duringRun();
  }


  // NotFound method
  function it_returns_itself_after_adding_a_404_handler() {
    $this->notFound( function() { return function() {}; } )->shouldBe( $this );
  }

  
  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    $this->config( 'debug' )->shouldBe( true );
  }


  // File method
  function it_builds_config_file_name_from_class() {
    $this->file()->shouldBe( 'router.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->file( 'router_custom.yml' );
    $this->file()->shouldBe( 'router_custom.yml' );
    $this->config( 'debug' )->shouldBe( false );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->file( 'router_custom.yml' );
    $this->file()->shouldBe( 'router_custom.yml' );
    $this->file( null );
    $this->file()->shouldBe( 'router.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->file( 'router_custom.yml' )->shouldBe( $this );
  }
}
