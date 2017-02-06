<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\App;
use Bulckens\AppTools\Database;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AppSpec extends ObjectBehavior {

  function let() {
    $this->beConstructedWith( 'test' );
  }


  // Run method
  function it_returns_itself_after_initializing_the_app() {
    $this->run()->shouldBe( $this );
  }

  function it_initializes_a_database_if_required_in_the_modules() {
    $this->run();
    $this->module( 'database' )->shouldHaveType( 'Bulckens\AppTools\Database' );
  }

  function it_does_not_initialize_a_database_if_missing_in_the_modules() {
    $this->file( 'app_database_missing.yml' )->run();
    $this->module( 'database' )->shouldBeNull();
  }

  function it_initializes_a_router_if_required_in_the_modules() {
    $this->run();
    $this->module( 'router' )->shouldHaveType( 'Bulckens\AppTools\Router' );
  }

  function it_does_not_initialize_a_router_if_missing_in_the_modules() {
    $this->file( 'app_router_missing.yml' )->run();
    $this->module( 'router' )->shouldBeNull();
  }

  function it_initializes_a_view_if_required_in_the_modules() {
    $this->run();
    $this->module( 'view' )->shouldHaveType( 'Bulckens\AppTools\View' );
  }

  function it_does_not_initialize_a_view_if_missing_in_the_modules() {
    $this->file( 'app_view_missing.yml' )->run();
    $this->module( 'view' )->shouldBeNull();
  }


  // Root method
  function it_finds_the_project_root() {
    $this->root()->shouldEndWith( '/app-tools/' );
  }

  function it_returns_the_root_with_the_given_path() {
    $this->root( 'with/path' )->shouldEndWith( '/app-tools/with/path' );
  }

  function it_uses_the_given_root_variable_if_defined() {
    $this->beConstructedWith( 'test', '/dev/alternative' );
    $this->root()->shouldEndWith( '/dev/alternative/' );
  }

  function it_ensures_a_tailing_slash_on_the_app_root() {
    $this->beConstructedWith( 'test', '/some/other/path/to/my/app' );
    $this->root()->shouldBe( '/some/other/path/to/my/app/' );
  }

  function it_fails_when_no_root_is_found() {
    $this->beConstructedWith( 'test', '/' );
    $this->shouldThrow( 'Bulckens\AppTraits\RootNotFoundException' )->duringRoot();
  }


  // Env method
  function it_returns_the_defined_environment() {
    $this->beConstructedWith( 'test' );
    $this->env()->shouldBe( 'test' );
  }

  function it_tests_positive_with_the_current_environment() {
    $this->beConstructedWith( 'test' );
    $this->env( 'test' )->shouldBe( true );
  }

  function it_tests_negative_with_the_wrong_environment() {
    $this->beConstructedWith( 'test' );
    $this->env( 'labs' )->shouldBe( false );
  }

  function it_tests_positive_with_the_current_environment_against_multiple_given() {
    $this->beConstructedWith( 'test' );
    $this->env([ 'test', 'development' ])->shouldBe( true );
  }


  // ToArray method
  function it_returns_an_array() {
    $this->toArray()->shouldBeArray();
  }

  function it_returns_an_array_with_the_env_parameter() {
    $this->toArray()->shouldHaveKeyWithValue( 'env', 'test' );
  }


  // CLI environment test
  function it_tests_positive_for_a_cli_environment() {
    $this::cli()->shouldBe( true );
  }


  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    $this->config( 'modules' )->shouldBeArray();
  }

  function it_returns_a_given_default_value_if_key_is_not_existing() {
    $this->config( 'flalalala', 500 )->shouldBe( 500 );
  }


  // File method
  function it_builds_config_file_name_from_class() {
    $this->file()->shouldBe( 'app.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->file( 'app_custom.yml' );
    $this->file()->shouldBe( 'app_custom.yml' );
    $this->config( 'custom' )->shouldBe( 'different' );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->file( 'app_custom.yml' );
    $this->file()->shouldBe( 'app_custom.yml' );
    $this->file( null );
    $this->file()->shouldBe( 'app.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->file( 'app_custom.yml' )->shouldBe( $this );
  }


  // Instance method
  function it_references_the_app_instance() {
    $this::instance()->shouldBe( $this );
  }


  // Module method
  function it_sets_and_gets_a_module() {
    $this->module( 'database', new Database() );
    $this->module( 'database' )->shouldHaveType( 'Bulckens\AppTools\Database' );
  }


  // Database method
  function it_returns_the_database_module() {
    $this->database()->shouldHaveType( 'Bulckens\AppTools\Database' );
  }

  function it_returns_nothing_if_no_database_is_defined() {
    $this->file( 'app_database_missing.yml' )->run();
    $this->database()->shouldBe( null );
  }


  // Router method
  function it_returns_the_router_module() {
    $this->router()->shouldHaveType( 'Bulckens\AppTools\Router' );
  }

  function it_returns_nothing_if_no_router_is_defined() {
    $this->file( 'app_router_missing.yml' )->run();
    $this->router()->shouldBe( null );
  }


  // View method
  function it_returns_the_view_module() {
    $this->view()->shouldHaveType( 'Bulckens\AppTools\View' );
  }

  function it_returns_nothing_if_no_view_is_defined() {
    $this->file( 'app_view_missing.yml' )->run();
    $this->view()->shouldBe( null );
  }


}
