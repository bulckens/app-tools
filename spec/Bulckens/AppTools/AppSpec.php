<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\App;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AppSpec extends ObjectBehavior {

  function let() {
    $this->beConstructedWith( 'test' );
  }


  // Root method
  function it_finds_the_project_root() {
    $this->root()->shouldEndWith( '/app-tools/' );
  }

  function it_returns_the_root_with_the_given_path() {
    $this->root( 'with/path' )->shouldEndWith( '/app-tools/with/path' );
  }

  function it_uses_the_given_root_variable_if_defined() {
    $this->beConstructedWith( 'test', dirname( dirname( dirname( __DIR__ ))) . '/dev/alternative' );
    $this->root()->shouldEndWith( '/dev/alternative' );
  }

  function it_ensures_a_tailing_slash_on_the_app_root() {
    // $this->beConstructedWith( 'test', '/some/other/path/to/my/app' );
    // $this->root()->shouldBe( '/some/other/path/to/my/app/' );
  }

  function it_fails_when_no_root_is_found() {
    $this->beConstructedWith( 'test', '/' );
    $this->shouldThrow( 'Bulckens\AppTools\RootNotFoundException' )->duringRoot();
  }


  // Env method
  function it_returns_the_defined_environment() {
    $this->beConstructedWith( 'test' );
    $this->env()->shouldBe( 'test' );
  }

  function it_tests_the_current_environment() {
    $this->beConstructedWith( 'test' );
    $this->env( 'test' )->shouldBe( true );
  }


  // CLI environment test
  function it_tests_positive_for_a_cli_environment() {
    $this->cli()->shouldBe( true );
  }


  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    // $this->config( 'modules' )->shouldBeArray();
  }

}
