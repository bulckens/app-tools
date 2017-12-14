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

  function letGo() {
    $this->clear( true );
  }


  // Run method
  function it_returns_itself_after_initializing_the_app() {
    $this->run()->shouldBe( $this );
  }

  function it_always_initializes_the_statistics_module() {
    $this->run();
    $this->module( 'statistics' )->shouldHaveType( 'Bulckens\AppTools\Statistics' );
  }

  function it_initializes_a_notifier_if_required_in_the_modules() {
    $this->run();
    $this->module( 'notifier' )->shouldHaveType( 'Bulckens\AppTools\Notifier' );
  }

  function it_does_not_initialize_a_notifier_if_missing_in_the_modules() {
    $this->configFile( 'app_notifier_missing.yml' )->run();
    $this->module( 'notifier' )->shouldBeNull();
  }

  function it_initializes_a_database_if_required_in_the_modules() {
    $this->run();
    $this->module( 'database' )->shouldHaveType( 'Bulckens\AppTools\Database' );
  }

  function it_does_not_initialize_a_database_if_missing_in_the_modules() {
    $this->configFile( 'app_database_missing.yml' )->run();
    $this->module( 'database' )->shouldBeNull();
  }

  function it_initializes_a_cache_module_if_required_in_the_modules() {
    $this->run();
    $this->module( 'cache' )->shouldHaveType( 'Bulckens\AppTools\Cache' );
  }

  function it_does_not_initialize_a_cache_module_if_missing_in_the_modules() {
    $this->configFile( 'app_cache_missing.yml' )->run();
    $this->module( 'cache' )->shouldBeNull();
  }

  function it_initializes_a_router_if_required_in_the_modules() {
    $this->run();
    $this->module( 'router' )->shouldHaveType( 'Bulckens\AppTools\Router' );
  }

  function it_does_not_initialize_a_router_if_missing_in_the_modules() {
    $this->configFile( 'app_router_missing.yml' )->run();
    $this->module( 'router' )->shouldBeNull();
  }

  function it_initializes_a_view_if_required_in_the_modules() {
    $this->run();
    $this->module( 'view' )->shouldHaveType( 'Bulckens\AppTools\View' );
  }

  function it_does_not_initialize_a_view_if_missing_in_the_modules() {
    $this->configFile( 'app_view_missing.yml' )->run();
    $this->module( 'view' )->shouldBeNull();
  }

  function it_initializes_a_user_if_required_in_the_modules() {
    $this->run();
    $this->module( 'user' )->shouldHaveType( 'Bulckens\AppTools\User' );
  }

  function it_does_not_initialize_a_user_if_missing_in_the_modules() {
    $this->configFile( 'app_user_missing.yml' )->run();
    $this->module( 'user' )->shouldBeNull();
  }

  function it_initializes_i18n_if_required_in_the_modules() {
    $this->run();
    $this->module( 'i18n' )->shouldHaveType( 'Bulckens\AppTools\I18n' );
  }

  function it_does_not_initialize_i18n_if_missing_in_the_modules() {
    $this->configFile( 'app_i18n_missing.yml' )->run();
    $this->module( 'i18n' )->shouldBeNull();
  }

  function it_initializes_a_module_with_a_custom_defined_class() {
    $this->configFile( 'app_custom_module.yml' )->run();
    $this->module( 'i18n' )->shouldHaveType( 'Bulckens\AppTests\TestI18n' );
  }

  function it_retains_any_registered_custom_modules_as_active_after_running() {
    $lala = new Database();
    $this->module( 'lala', $lala );
    $this->run();
    $this->module( 'lala' )->shouldBe( $lala );
  }

  function it_runs_only_with_the_statistics_module_if_no_modules_are_configured() {
    $this->configFile( 'app_moduleless.yml' )->run();
    $modules = $this->modules();
    $modules->shouldHaveCount( 1 );
    $modules[0]->shouldBe( 'statistics' );
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
    $this->configFile()->shouldBe( 'app.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->configFile( 'app_custom.yml' );
    $this->configFile()->shouldBe( 'app_custom.yml' );
    $this->config( 'custom' )->shouldBe( 'different' );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->configFile( 'app_custom.yml' );
    $this->configFile()->shouldBe( 'app_custom.yml' );
    $this->configFile( null );
    $this->configFile()->shouldBe( 'app.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->configFile( 'app_custom.yml' )->shouldBe( $this );
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

  function it_returns_itself_after_setting_a_module() {
    $this->module( 'database', new Database() )->shouldBe( $this );
  }

  function it_created_a_named_method_for_registered_custom_modules() {
    $flas = new Database();
    $this->module( 'flas', $flas );
    $this->flas()->shouldBe( $flas );
  }



  // Modules method
  function it_returns_a_list_of_registered_modules() {
    $this->configFile( 'app.yml' );
    $this->run();
    $this->modules()->shouldContain( 'cache' );
    $this->modules()->shouldContain( 'database' );
    $this->modules()->shouldContain( 'notifier' );
    $this->modules()->shouldContain( 'router' );
    $this->modules()->shouldContain( 'user' );
    $this->modules()->shouldContain( 'view' );
    $this->modules()->shouldContain( 'i18n' );
  }

  function it_returns_no_modules_if_none_are_registered() {
    $this->configFile( 'app_moduleless.yml' );
    $this->modules()->shouldNotContain( 'cache' );
    $this->modules()->shouldNotContain( 'database' );
    $this->modules()->shouldNotContain( 'notifier' );
    $this->modules()->shouldNotContain( 'router' );
    $this->modules()->shouldNotContain( 'user' );
    $this->modules()->shouldNotContain( 'view' );
    $this->modules()->shouldNotContain( 'i18n' );
  }


  // Clear method
  function it_removes_all_registered_bundled_modules() {
    $this->run();
    $this->modules()->shouldContain( 'cache' );
    $this->modules()->shouldContain( 'database' );
    $this->modules()->shouldContain( 'notifier' );
    $this->modules()->shouldContain( 'router' );
    $this->modules()->shouldContain( 'user' );
    $this->modules()->shouldContain( 'view' );
    $this->modules()->shouldContain( 'i18n' );
    $this->clear();
    $this->modules()->shouldNotContain( 'cache' );
    $this->modules()->shouldNotContain( 'database' );
    $this->modules()->shouldNotContain( 'notifier' );
    $this->modules()->shouldNotContain( 'router' );
    $this->modules()->shouldNotContain( 'user' );
    $this->modules()->shouldNotContain( 'view' );
    $this->modules()->shouldNotContain( 'i18n' );
  }

  function it_removes_no_registered_custom_modules() {
    $this->module( 'fropy', new Database() );
    $this->clear();
    $this->modules()->shouldContain( 'fropy' );
  }

  function it_forces_registered_custom_modules_to_be_removed() {
    $this->module( 'fropy', new Database() );
    $this->clear( true );
    $this->modules()->shouldNotContain( 'fropy' );
  }

  function it_returns_itself_after_clearing() {
    $this->clear()->shouldBe( $this );
  }

  // Dynamic methods (__call)
  function it_returns_the_cache_module() {
    $this->run()->cache()->shouldHaveType( 'Bulckens\AppTools\Cache' );
  }

  function it_returns_nothing_if_no_cache_is_defined() {
    $this->configFile( 'app_cache_missing.yml' )->run();
    $this->run()->cache()->shouldBe( null );
  }

  function it_returns_the_database_module() {
    $this->run()->database()->shouldHaveType( 'Bulckens\AppTools\Database' );
  }

  function it_returns_nothing_if_no_database_is_defined() {
    $this->configFile( 'app_database_missing.yml' )->run();
    $this->run()->database()->shouldBe( null );
  }

  function it_returns_the_notifier_module() {
    $this->run()->notifier()->shouldHaveType( 'Bulckens\AppTools\Notifier' );
  }

  function it_returns_nothing_if_no_notifier_is_defined() {
    $this->configFile( 'app_notifier_missing.yml' )->run();
    $this->run()->notifier()->shouldBe( null );
  }

  function it_returns_the_router_module() {
    $this->run()->router()->shouldHaveType( 'Bulckens\AppTools\Router' );
  }

  function it_returns_nothing_if_no_router_is_defined() {
    $this->configFile( 'app_router_missing.yml' )->run();
    $this->run()->router()->shouldBe( null );
  }

  function it_returns_the_view_module() {
    $this->run()->view()->shouldHaveType( 'Bulckens\AppTools\View' );
  }

  function it_returns_nothing_if_no_view_is_defined() {
    $this->configFile( 'app_view_missing.yml' )->run();
    $this->run()->view()->shouldBe( null );
  }

  function it_returns_the_i18n_module() {
    $this->run()->i18n()->shouldHaveType( 'Bulckens\AppTools\I18n' );
  }

  function it_returns_nothing_if_no_i18n_is_defined() {
    $this->configFile( 'app_i18n_missing.yml' )->run();
    $this->run()->i18n()->shouldBe( null );
  }

  function it_fails_for_non_existant_methods() {
    $this->shouldThrow( 'Bulckens\AppTools\AppMethodMissingException' )->duringFlamunitrapus();
  }


  // Version method
  function it_returns_the_current_version_tag() {
    $this->version()->shouldMatch( '/\d+\.\d+\.\d+/' );
  }


}
