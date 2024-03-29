<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\Router;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RouterSpec extends ObjectBehavior {

  function let() {
    $this->notFound( function( $c ) {
      return function( $res, $req ) use( $c ) {
        return $c['response']->write( '' );
      };
    });
  }

  function letGo() {
    $this->configFile( null );
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
    $this->configFile( 'router_fail.yml' );
    $this->shouldThrow( 'Bulckens\AppTools\RouterRoutesRootNotDefinedException' )->duringRun();
  }


  // Error method
  function it_returns_itself_after_adding_an_error_handler() {
    $this->error( function() { return function() {}; } )->shouldBe( $this );
  }


  // NotFound method
  function it_returns_itself_after_adding_a_not_found_handler() {
    $this->notFound( function() { return function() {}; } )->shouldBe( $this );
  }


  // NotAllowed method
  function it_returns_itself_after_adding_a_not_allowed_handler() {
    $this->notAllowed( function() { return function() {}; } )->shouldBe( $this );
  }

  
  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    $this->config( 'debug' )->shouldBe( true );
  }

  function it_returns_a_given_default_value_if_key_is_not_existing() {
    $this->config( '123', [ 1, 2, 3 ] )->shouldBe( [ 1, 2, 3 ] );
  }


  // File method
  function it_builds_config_file_name_from_class() {
    $this->configFile()->shouldBe( 'router.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->configFile( 'router_custom.yml' );
    $this->configFile()->shouldBe( 'router_custom.yml' );
    $this->config( 'debug' )->shouldBe( false );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->configFile( 'router_custom.yml' );
    $this->configFile()->shouldBe( 'router_custom.yml' );
    $this->configFile( null );
    $this->configFile()->shouldBe( 'router.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->configFile( 'router_custom.yml' )->shouldBe( $this );
  }


  // Engine method
  function it_returns_the_render_engine() {
    $this->run();
    $this->engine()->shouldHaveType( 'Slim\App' );
  }
}
