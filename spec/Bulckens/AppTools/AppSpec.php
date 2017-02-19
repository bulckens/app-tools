<?php

namespace spec\Bulckens\AppTools;

use stdClass;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Database;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AppSpec extends ObjectBehavior {

  function let() {
    $this->beConstructedWith( 'dev' );
  }


  // Run method
  function it_returns_itself_after_initializing_the_app() {
    $this->run()->shouldBe( $this );
  }

  function it_initializes_a_notifier_if_required_in_the_modules() {
    $this->run();
    $this->module( 'notifier' )->shouldHaveType( 'Bulckens\AppTools\Notifier' );
  }

  function it_does_not_initialize_a_notifier_if_missing_in_the_modules() {
    $this->file( 'app_notifier_missing.yml' )->run();
    $this->module( 'notifier' )->shouldBeNull();
  }

  function it_initializes_a_database_if_required_in_the_modules() {
    $this->run();
    $this->module( 'database' )->shouldHaveType( 'Bulckens\AppTools\Database' );
  }

  function it_does_not_initialize_a_database_if_missing_in_the_modules() {
    $this->file( 'app_database_missing.yml' )->run();
    $this->module( 'database' )->shouldBeNull();
  }

  function it_initializes_a_cache_module_if_required_in_the_modules() {
    $this->run();
    $this->module( 'cache' )->shouldHaveType( 'Bulckens\AppTools\Cache' );
  }

  function it_does_not_initialize_a_cache_module_if_missing_in_the_modules() {
    $this->file( 'app_cache_missing.yml' )->run();
    $this->module( 'cache' )->shouldBeNull();
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

  function it_initializes_a_user_if_required_in_the_modules() {
    $this->run();
    $this->module( 'user' )->shouldHaveType( 'Bulckens\AppTools\User' );
  }

  function it_does_not_initialize_a_user_if_missing_in_the_modules() {
    $this->file( 'app_user_missing.yml' )->run();
    $this->module( 'user' )->shouldBeNull();
  }


  // Root method
  function it_finds_the_project_root() {
    $this->root()->shouldEndWith( '/app-tools/' );
  }

  function it_returns_the_root_with_the_given_path() {
    $this->root( 'with/path' )->shouldEndWith( '/app-tools/with/path' );
  }

  function it_uses_the_given_root_variable_if_defined() {
    $this->beConstructedWith( 'dev', '/dev/alternative' );
    $this->root()->shouldBe( '/dev/alternative/' );
  }

  function it_finds_a_parent_directory_a_number_of_levels_up() {
    $this->beConstructedWith( 'dev', __DIR__, 2 );
    $this->root()->shouldEndWith( '/app-tools/spec/' );
  }

  function it_ensures_a_tailing_slash_on_the_app_root() {
    $this->beConstructedWith( 'dev', '/some/other/path/to/my/app' );
    $this->root()->shouldBe( '/some/other/path/to/my/app/' );
  }

  function it_fails_when_no_root_is_found() {
    $this->beConstructedWith( 'dev', '/' );
    $this->shouldThrow( 'Bulckens\AppTools\RootNotFoundException' )->duringRoot();
  }

  // Env method
  function it_returns_the_defined_environment() {
    $this->beConstructedWith( 'dev' );
    $this->env()->shouldBe( 'dev' );
  }

  function it_tests_positive_with_the_current_environment() {
    $this->beConstructedWith( 'dev' );
    $this->env( 'dev' )->shouldBe( true );
  }

  function it_tests_negative_with_the_wrong_environment() {
    $this->beConstructedWith( 'dev' );
    $this->env( 'labs' )->shouldBe( false );
  }

  function it_tests_positive_with_the_current_environment_against_multiple_given() {
    $this->beConstructedWith( 'dev' );
    $this->env([ 'dev', 'development' ])->shouldBe( true );
  }

  function it_tests_positive_with_the_current_environment_against_multiple_arguments_given() {
    $this->beConstructedWith( 'dev' );
    $this->env( 'development', 'dev' )->shouldBe( true );
  }


  // ToArray method
  function it_returns_an_array() {
    $this->toArray()->shouldBeArray();
  }

  function it_returns_an_array_with_the_env_parameter() {
    $this->toArray()->shouldHaveKeyWithValue( 'env', 'dev' );
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


  // Get method
  function it_references_the_app_instance() {
    $this::get()->shouldBe( $this );
  }


  // Module method
  function it_sets_and_gets_a_module() {
    $this->module( 'database', new Database() );
    $this->module( 'database' )->shouldHaveType( 'Bulckens\AppTools\Database' );
  }


  // Dynamic module methods (__call)
  function it_returns_the_cache_module() {
    $this->cache()->shouldHaveType( 'Bulckens\AppTools\Cache' );
  }

  function it_returns_nothing_if_no_cache_is_defined() {
    $this->file( 'app_cache_missing.yml' )->run();
    $this->cache()->shouldBe( null );
  }

  function it_returns_the_database_module() {
    $this->database()->shouldHaveType( 'Bulckens\AppTools\Database' );
  }

  function it_returns_nothing_if_no_database_is_defined() {
    $this->file( 'app_database_missing.yml' )->run();
    $this->database()->shouldBe( null );
  }

  function it_returns_the_notifier_module() {
    $this->notifier()->shouldHaveType( 'Bulckens\AppTools\Notifier' );
  }

  function it_returns_nothing_if_no_notifier_is_defined() {
    $this->file( 'app_notifier_missing.yml' )->run();
    $this->notifier()->shouldBe( null );
  }

  function it_returns_the_router_module() {
    $this->router()->shouldHaveType( 'Bulckens\AppTools\Router' );
  }

  function it_returns_nothing_if_no_router_is_defined() {
    $this->file( 'app_router_missing.yml' )->run();
    $this->router()->shouldBe( null );
  }

  function it_returns_the_view_module() {
    $this->view()->shouldHaveType( 'Bulckens\AppTools\View' );
  }

  function it_returns_nothing_if_no_view_is_defined() {
    $this->file( 'app_view_missing.yml' )->run();
    $this->view()->shouldBe( null );
  }

  function it_fails_for_non_existant_methods() {
    $this->shouldThrow( 'Bulckens\AppTools\AppMethodMissingException' )->duringFlamunitrapus();
  }


}
